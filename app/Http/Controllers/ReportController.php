<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\AppSettings;
use App\Bazaar;
use App\SalesType;
use App\CurrentVendor;


use App\Vendor;
use Carbon\Carbon;

use Illuminate\Http\Request;

use Auth;
use Excel;
use ModelNotFoundException;

class ReportController extends Controller
{

	protected $current_bazaar;
	protected $fee;
	protected $creditCard;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->middleware('auth');

		// Grab app settings
		$this->current_bazaar = AppSettings::bazaar();
		$this->fee            = AppSettings::fee();
		$this->creditCard     = AppSettings::creditCardFee();
	}

	/**
	 * Display the main page
	 *
	 * @return void
	 * @author Hannah
	 */
	public function index()
	{
		$bazaar = Bazaar::find($this->current_bazaar);
		$data   = array();

		// Bazaar totals by day
		$i = 0;
		for ($date = $bazaar->start_date; $date->lte($bazaar->end_date); $date->addDay()) {
			$data["bazaar-tab-$i"] = [
				'title' => $date->format('F j'),
				'data' => $this->allVendorsByDate($bazaar, $date),
				'date' => $date->format('m-d-Y'),
			];
			++$i;
		}

		// Bazaar roll-up
		$rollup = $this->allVendorsRollUp($bazaar);

		$ccFee      = $this->creditCard * 100;
		$fee        = $this->fee * 100;
		$rollupCols = [
			'Total Cash Sales',
			'Total Credit Card Sales',
			"Credit Card Fee ($ccFee%)",
			'Total Layaway Sales',
			'Total Sales',
			"BBB Fee ($fee%)",
		];

		// Checked out column
		$checked_out = array();
		foreach($bazaar->vendors as $vendor) {
			if($vendor->pivot->checked_out) {
				$checked_out[] = $vendor->pivot->vendor_number;
			}
		}

		if (!$bazaar->isValidated()) {
			$warning = "WARNING: This bazaar still has non-validated or incorrect sales sheets. This total may not be accurate.";
		}

		$params = array(
			'bazaar' => $bazaar->name,
			'data' => $data,
			'rollup' => $rollup,
			'rollupCols' => $rollupCols,
			'checked_out' => $checked_out,
		);

		if (isset($warning))
			$params['warning'] = $warning;

		return view('chair.reports.index', $params);
	}

	/**
	 * @param Request $request
	 * @return int
	 */
	public function checkout(Request $request)
	{
		$id      = $request->get('id');
		$checked = $request->get('checked') === 'true' ? true : false;

		$v      = Vendor::join('bazaar_vendor', 'bazaar_vendor.vendor_id', '=', 'vendors.id')
			->select('vendors.id')
			->where('bazaar_vendor.bazaar_id', '=', $this->current_bazaar)
			->where('bazaar_vendor.vendor_number', '=', $id)->first();
		$vendor = Vendor::find($v->id);
		$vendor->bazaars()->updateExistingPivot($this->current_bazaar, [
			'checked_out' => $checked
		]);

		return 1;
	}

	/**
	 * Handles a vendor search request
	 * @param $request
	 * @return view with vendor information
	 * @author Hannah
	 */
	public function vendor(Request $request)
	{
		$vendor = CurrentVendor::fromString($request->input('vendor'));

		// Date report
		$bazaar = Bazaar::find($this->current_bazaar);
		$dates  = array();
		$id     = 0;

		for ($date = $bazaar->start_date; $date->lte($bazaar->end_date); $date->addDay()) {
			$row = [
				SalesType::CASH => 0,
				SalesType::CARD => 0,
				SalesType::LAYAWAY => 0,
				'total' => 0,
				'sheets' => array(),
			];
			foreach ($vendor->salesSheets() as $sheet) {
				if ($sheet->date_of_sales->isSameDay($date)) {
					$row['sheets'][$sheet->sheet_number] = [
						SalesType::CASH => $sheet->cash(),
						SalesType::CARD => $sheet->credit(),
						SalesType::LAYAWAY => $sheet->layaway(),
						'total' => $sheet->totalSales()
					];
					foreach ($sheet->sales as $sale) {
						$row[$sale->sales_type] += $sale->amount;
						$row['total'] += $sale->amount;
					}
				}
			}

			$id++;
			$row['id']                     = "date-$id";
			$row['day']                    = $date->format('l');
			$row['daily-date']             = $date->format('m-d-Y');
			$dates[$date->format('m/d/Y')] = $row;
		}

		if (!$vendor->isValidated()) {
			$warning = 'WARNING: This vendor still has non-validated or incorrect sheets. This total may not be accurate.';
		}

		return view('chair.reports.vendor', compact('vendor', 'dates', 'warning'));
	}


	/**
	 * Returns an Excel spreadsheet with vendor sales totals, one vendor per line
	 * or a page to the view the vendor sales totals.
	 *
	 * @param $bazaar
	 * @param $date
	 * @param bool|true $view
	 * @return array
	 */
	private function allVendorsByDate($bazaar, $date, $view = true)
	{
		// Sheet data
		$data   = array();
		$totals = array(
			SalesType::CASH => 0,
			SalesType::CARD => 0,
			SalesType::LAYAWAY => 0,
			'Total' => 0,
		);

		// Create data for spreadsheet
		foreach ($bazaar->vendors as $i => $vendor) {

			$row = array();

			$row['Vendor Number']    = $vendor->vendorNumber($bazaar) . " - " . $vendor->name;
			$row[SalesType::CASH]    = 0;
			$row[SalesType::CARD]    = 0;
			$row[SalesType::LAYAWAY] = 0;

			foreach ($vendor->salesSheets as $sheet) {

				if ($sheet->date_of_sales->isSameDay($date)) {

					foreach ($sheet->sales as $sale) {
						switch ($sale->sales_type) {
							case SalesType::CASH:
								$row[SalesType::CASH] += $sale->amount;
								$totals[SalesType::CASH] += $sale->amount;
								break;
							case SalesType::CARD:
								$row[SalesType::CARD] += $sale->amount;
								$totals[SalesType::CARD] += $sale->amount;
								break;
							case SalesType::LAYAWAY:
								$row[SalesType::LAYAWAY] += $sale->amount;
								$totals[SalesType::LAYAWAY] += $sale->amount;
								break;
						}
					}
				}
			}

			// Calculate our "totals" column, based on format
			if ($view) {
				$row['Total'] =
					$row[SalesType::CASH] +
					$row[SalesType::CARD] +
					$row[SalesType::LAYAWAY];
				$totals['Total'] += $row['Total'];
			} else {
				$j            = $i + 2;
				$row['Total'] = "=SUM(\$B$j:\$D$j)";
			}
			$data[] = $row;
		}

		$data['totals'] = $totals;
		return $data;
	}

	/**
	 * Returns an Excel spread
	 *
	 * @param $bazaar The bazaar to rollup
	 * @param $view bool, true returns viewable data, false returns Excel data
	 * @return array
	 * @author Hannah
	 */
	private function allVendorsRollUp($bazaar, $view = true)
	{
		$fee        = $this->fee * 100;
		$ccFee      = $this->creditCard * 100;
		$data       = array();
		$checkedout = array();
		$totals_row = array(
			'Vendor Number' => 'TOTALS',
			'Vendor Name' => '',
			'Total Cash Sales' => 0,
			'Total Credit Card Sales' => 0,
			"Credit Card Fee ($ccFee%)" => 0,
			'Total Layaway Sales' => 0,
			'Total Sales' => 0,
			"BBB Fee ($fee%)" => 0,
			'Payment Currency' => ''
		);

		foreach ($bazaar->vendors as $i => $vendor) {
			$totals = array(
				SalesType::CASH => 0,
				SalesType::CARD => 0,
				SalesType::LAYAWAY => 0
			);

			foreach ($vendor->salesSheets as $sheet) {

				if ($sheet->bazaar_id == $bazaar->id) {

					foreach ($sheet->sales as $sale) {
						switch ($sale->sales_type) {
							case SalesType::CASH:
								$totals[SalesType::CASH] += $sale->amount;
								break;
							case SalesType::CARD:
								$totals[SalesType::CARD] += $sale->amount;
								break;
							case SalesType::LAYAWAY:
								$totals[SalesType::LAYAWAY] += $sale->amount;
								break;
						}
					}
				}
			}

			if ($view) {
				// Calculate totals, fees
				$grandTotal = $totals[SalesType::CASH] + $totals[SalesType::CARD] + $totals[SalesType::LAYAWAY];
				$feeData    = round($this->fee * $grandTotal, 2);
				$ccFeeData  = round($totals[SalesType::CARD] * $this->creditCard, 2);

				// Add to our "totals" row
				$totals_row['Total Cash Sales'] += $totals[SalesType::CASH];
				$totals_row['Total Credit Card Sales'] += $totals[SalesType::CARD];
				$totals_row["Credit Card Fee ($ccFee%)"] += $ccFeeData;
				$totals_row['Total Layaway Sales'] += $totals[SalesType::LAYAWAY];
				$totals_row['Total Sales'] += $grandTotal;
				$totals_row["BBB Fee ($fee%)"] += $feeData;

			} else {
				$j = $i + 2;
				// Calculate totals, fees
				$grandTotal = "=SUM(\$C$j,\$D$j,\$F$j)";
				$feeData    = "=\$G$j*" . $this->fee;
				$ccFeeData  = "=\$D$j*" . $this->creditCard;
			}

			// Store data
			$row = array(
				'Vendor Number' => $vendor->vendorNumber($bazaar),
				'Vendor Name' => $vendor->name,
				'Total Cash Sales' => $totals[SalesType::CASH],
				'Total Credit Card Sales' => $totals[SalesType::CARD],
				"Credit Card Fee ($ccFee%)" => $ccFeeData,
				'Total Layaway Sales' => $totals[SalesType::LAYAWAY],
				'Total Sales' => $grandTotal,
				"BBB Fee ($fee%)" => $feeData,
				'Payment Currency' => $vendor->payment,
			);

			$data[] = $row;
		}

		if ($view)
			$data['totals'] = $totals_row;

		return $data;
	}

	/**
	 *
	 */
	public function rollupExcel()
	{

		$bazaar = Bazaar::find($this->current_bazaar);

		// Date Data
		$date_data = array();
		$i         = 0;

		for ($date = $bazaar->start_date; $date->lte($bazaar->end_date); $date->addDay()) {
			$date_data[] = [
				'title' => $date->format('F j'),
				'data' => $this->allVendorsByDate($bazaar, $date, false)
			];
			++$i;
		}

		// Rollup Data
		$rollup_data = $this->allVendorsRollUp($bazaar, false);


		// Download to Excel Spreadsheet if we don't want to view
		$bazaarName = preg_replace('/\s+/', '', $bazaar->name);
		Excel::create("{$bazaarName}-Rollup", function ($excel) use ($date_data, $rollup_data) {
			$excel->setTitle("Rollup");

			// Sheets by date
			foreach ($date_data as $data) {
				$excel->sheet($data['title'], function ($sheet) use ($data) {
					$sheet->setColumnFormat(array(
						'B' => '"$"#,##0.00_-',
						'C' => '"$"#,##0.00_-',
						'D' => '"$"#,##0.00_-',
						'E' => '"$"#,##0.00_-'
					));
					$sheet->fromArray($data['data']);
				});
			}

			// Rollup sheet
			$excel->sheet('rollup', function ($sheet) use ($rollup_data) {
				$sheet->setColumnFormat(array(
					'C' => '"$"#,##0.00_-',
					'D' => '"$"#,##0.00_-',
					'E' => '"$"#,##0.00_-',
					'F' => '"$"#,##0.00_-',
					'G' => '"$"#,##0.00_-',
					'H' => '"$"#,##0.00_-',
				));
				$sheet->fromArray($rollup_data);
			});

			return $excel->setCreator(Auth::user()->name)->download('xlsx');

		});
	}

	/**
	 * @param $id The vendor number to download
	 */
	public function vendorExcel($id)
	{
		$vendor  = CurrentVendor::whereVendorNumber($id)->firstOrFail();
		$bazaar  = Bazaar::find($this->current_bazaar);
		$data    = array();
		$j       = 0;
		$m_count = array();

		foreach ($vendor->salesSheets() as $i => $sheet) {
			$j = $i + 2;

			$data['By Sales Sheet'][] = [
				'Date' => $sheet->date_of_sales->format('m/d/Y'),
				'Sheet Number' => $sheet->sheet_number,
				'Cash Totals' => $sheet->cash(),
				'Credit Card Totals' => $sheet->credit(),
				'Layaway Totals' => $sheet->layaway(),
				'Total Sales' => "=SUM(\$C$j:\$E$j)"
			];
		}

		$i = 1;
		for ($date = $bazaar->start_date; $date->lte($bazaar->end_date); $date->addDay()) {
			$cash    = "";
			$credit  = "";
			$layaway = "";
			++$i;

			$m = 1;
			foreach ($data['By Sales Sheet'] as $k => $sheet) {
				$l = $k + 2;
				if (Carbon::createFromFormat('m/d/Y', $sheet['Date'])->isSameDay($date)) {
					++$m;
					$cash .= "'By Sales Sheet'!C$l,";
					$credit .= "'By Sales Sheet'!D$l,";
					$layaway += "'By Sales Sheet'!E$l,";

					$data['days'][$date->format('l') . " Summary"][] = [
						'Sheet Number' => "='By Sales Sheet'!B$l",
						'Cash Totals' => "='By Sales Sheet'!C$l",
						'Credit Card Totals' => "='By Sales Sheet'!D$l",
						'Layaway Totals' => "='By Sales Sheet'!E$l",
						'Total Sales' => "=SUM(B$m:D$m)",
					];
				}
			}

			$m_count[] = $m;

			if ($cash != "")
				$cash = "=SUM($cash)";
			if ($credit != "")
				$credit = "=SUM($credit)";
			if ($layaway != "")
				$layaway = "=SUM($layaway)";

			$data['By Date'][] = [
				'Date' => $date->format('m/d/Y'),
				'Cash Totals' => $cash,
				'Credit Card Totals' => $credit,
				'Layaway Totals' => $layaway,
				'Total Sales' => "=SUM(\$B$i:\$D$i)"
			];
		}

		// Download to Excel Spreadsheet
		$vendor_name = preg_replace('/\s+/', '', $vendor->name);
		Excel::create("{$vendor_name}-Rollup", function ($excel) use ($data, $vendor_name, $i, $j, $m_count) {
			$excel->setTitle($vendor_name);

			$excel->sheet('By Sales Sheet', function ($sheet) use ($data, $j) {
				$sheet->setColumnFormat(array(
					'C' => '"$"#,##0.00_-',
					'D' => '"$"#,##0.00_-',
					'E' => '"$"#,##0.00_-',
					'F' => '"$"#,##0.00_-'
				));
				$sheet->fromArray($data['By Sales Sheet']);
				$sheet->appendRow(array(
					'TOTAL:',
					'',
					"=SUM(\$C2:\$C$j)",
					"=SUM(\$D2:\$D$j)",
					"=SUM(\$E2:\$E$j)",
					"=SUM(\$F2:\$F$j)"
				));
			});

			$excel->sheet('By Date', function ($sheet) use ($data, $i) {
				$sheet->setColumnFormat(array(
					'B' => '"$"#,##0.00_-',
					'C' => '"$"#,##0.00_-',
					'D' => '"$"#,##0.00_-',
					'E' => '"$"#,##0.00_-'
				));
				$sheet->fromArray($data['By Date']);
				$sheet->appendRow(array(
					'TOTAL:',
					"=SUM(\$B2:\$B$i)",
					"=SUM(\$C2:\$C$i)",
					"=SUM(\$D2:\$D$i)",
					"=SUM(\$E2:\$E$i)",
				));
			});

			$m = 0;
			foreach ($data['days'] as $key => $day) {
				$excel->sheet($key, function ($sheet) use ($day, $m_count, $m) {
					$sheet->setColumnFormat(array(
						'B' => '"$"#,##0.00_-',
						'C' => '"$"#,##0.00_-',
						'D' => '"$"#,##0.00_-',
						'E' => '"$"#,##0.00_-'
					));
					$sheet->fromArray($day);
					$sheet->appendRow(array(
						'TOTAL:',
						"=SUM(\$B2:\$B{$m_count[$m]})",
						"=SUM(\$C2:\$C{$m_count[$m]})",
						"=SUM(\$D2:\$D{$m_count[$m]})",
						"=SUM(\$E2:\$E{$m_count[$m]})",
					));
				});
				++$m;
			}

			return $excel->setCreator(Auth::user()->name)->download('xlsx');

		});
	}

	public function invoice($id)
	{
		$vendor = CurrentVendor::whereVendorNumber($id)->first();
		$bazaar = Bazaar::find($this->current_bazaar);
		$cc_fee = AppSettings::whereName('credit_card_fee')->first();
		$b_fee  = AppSettings::whereName('bazaar_fee')->first();
		$cc_fee = $cc_fee->setting;
		$b_fee  = $b_fee->setting;
		$data   = array();
		$totals = array();

		for ($date = $bazaar->start_date; $date->lte($bazaar->end_date); $date->addDay()) {
			$d = $date->format('j-M-Y');

			$data[$d] = [
				'cash' => 0,
				'credit' => 0,
				'layaway' => 0,
			];

			// Go through each sheet by date and add to daily total
			foreach ($vendor->salesSheets() as $sheet) {
				if ($sheet->date_of_sales->isSameDay($date)) {
					$data[$d]['cash'] += $sheet->cash();
					$data[$d]['credit'] += $sheet->credit();
					$data[$d]['layaway'] += $sheet->layaway();
				}
			}

			$data[$d]['cc_fee'] = $data[$d]['credit'] * ($cc_fee / 100);
			$data[$d]['total']  = $data[$d]['cash'] + $data[$d]['credit'] + $data[$d]['layaway'];

			$cc_fee_total = $vendor->credit() * ($cc_fee / 100);
			$b_fee_total = $vendor->totalSales() * ($b_fee / 100);

			// Totals
			$totals['cash']         = number_format($vendor->cash(), 2);
			$totals['credit']       = number_format($vendor->credit(), 2);
			$totals['layaway']      = number_format($vendor->layaway(), 2);
			$totals['total']        = number_format($vendor->totalSales(), 2);
			$totals['cc_fee']       = number_format($cc_fee_total, 2);
			$totals['b_fee']        = number_format($b_fee_total, 2);
			$totals['deduct']       = number_format($cc_fee_total + $b_fee_total + $vendor->table_fee + $vendor->audit_adjust, 2);
			$totals['owed']         = number_format($vendor->totalSales() - $totals['deduct'], 2);
			$totals['table_fee']    = number_format($vendor->table_fee, 2);
			$totals['audit_adjust'] = number_format($vendor->audit_adjust, 2);

			// Format for pretty print
			$data[$d]['cash']    = number_format($data[$d]['cash'], 2);
			$data[$d]['credit']  = number_format($data[$d]['credit'], 2);
			$data[$d]['layaway'] = number_format($data[$d]['layaway'], 2);
			$data[$d]['cc_fee']  = number_format($data[$d]['cc_fee'], 2);
			$data[$d]['total']   = number_format($data[$d]['total'], 2);
		}

		$fees = array(
			'bazaar' => $b_fee,
			'credit' => $cc_fee
		);

		return view('chair.reports.invoice', compact('vendor', 'bazaar', 'fees', 'data', 'totals'));
	}

	public function daily(Request $request, $id = null)
	{
		$date   = Carbon::createFromFormat('m-d-Y', $request->input('date'));
		$bazaar = Bazaar::find($this->current_bazaar);
		$totals = array();

		if ($id !== null) {
			$vendors = array(CurrentVendor::find($id));
		} else {
			$vendors = CurrentVendor::all();
		}
		foreach ($vendors as $vendor) {
			$totals[$vendor->id] = array(
				'cash' => 0,
				'credit' => 0,
				'layaway' => 0,
				'total' => 0,
			);

			foreach ($vendor->salesSheets() as $sheet) {
				if ($sheet->date_of_sales->isSameDay($date)) {
					$totals[$vendor->id]['cash'] += $sheet->cash();
					$totals[$vendor->id]['credit'] += $sheet->credit();
					$totals[$vendor->id]['layaway'] += $sheet->layaway();
					$totals[$vendor->id]['total'] += $sheet->totalSales();
				}
			}

			$totals[$vendor->id]['cash']    = number_format($totals[$vendor->id]['cash'], 2);
			$totals[$vendor->id]['credit']  = number_format($totals[$vendor->id]['credit'], 2);
			$totals[$vendor->id]['layaway'] = number_format($totals[$vendor->id]['layaway'], 2);
			$totals[$vendor->id]['total']   = number_format($totals[$vendor->id]['total'], 2);

			if ($date->isSameDay($bazaar->start_date) && $vendor->table_fee != 0) {
				$totals[$vendor->id]['table_fee'] = number_format($vendor->table_fee, 2);
			}
		}
		return view('chair.reports.daily', compact('date', 'vendors', 'totals', 'bazaar'));
	}
}
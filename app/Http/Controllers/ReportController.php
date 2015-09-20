<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\AppSettings;
use App\Bazaar;
use App\SalesType;
use App\CurrentVendor;


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
				'data' => $this->allVendorsByDate($bazaar, $date)
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

		if (!$bazaar->isValidated()) {
			$warning = "WARNING: This bazaar still has non-validated or incorrect sales sheets. This total may not be accurate.";
		}

		$params = array(
			'bazaar' => $bazaar->name,
			'data' => $data,
			'rollup' => $rollup,
			'rollupCols' => $rollupCols,
		);

		if (isset($warning))
			$params['warning'] = $warning;

		return view('chair.reports.index', $params);
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
	 * @param $view
	 * @return void
	 * @author Hannah
	 */
	private function allVendorsByDate($bazaar, $date, $view = true)
	{
		// Sheet data
		$data = array();

		// Create data for spreadsheet
		foreach ($bazaar->vendors as $i => $vendor) {

			$row = array();

			$row['Vendor Number']    = $vendor->vendorNumber($bazaar);
			$row[SalesType::CASH]    = 0;
			$row[SalesType::CARD]    = 0;
			$row[SalesType::LAYAWAY] = 0;

			foreach ($vendor->salesSheets as $sheet) {

				if ($sheet->date_of_sales->isSameDay($date)) {

					foreach ($sheet->sales as $sale) {
						switch ($sale->sales_type) {
							case SalesType::CASH:
								$row[SalesType::CASH] += $sale->amount;
								break;
							case SalesType::CARD:
								$row[SalesType::CARD] += $sale->amount;
								break;
							case SalesType::LAYAWAY:
								$row[SalesType::LAYAWAY] += $sale->amount;
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
			} else {
				$j            = $i + 2;
				$row['Total'] = "=SUM(\$B$j:\$D$j)";
			}
			$data[] = $row;
		}

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
		$data = array();

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

			$fee   = $this->fee * 100;
			$ccFee = $this->creditCard * 100;
			if ($view) {
				// Calculate totals, fees
				$grandTotal = $totals[SalesType::CASH] + $totals[SalesType::CARD] + $totals[SalesType::LAYAWAY];
				$feeData    = round($this->fee * $grandTotal, 2);
				$ccFeeData  = round($totals[SalesType::CARD] * $this->creditCard, 2);
			} else {
				$j = $i + 2;
				// Calculate totals, fees
				$grandTotal = "=SUM(\$C$j,\$D$j,\$F$j)";
				$feeData    = "=\$G$j*" . $this->fee;
				$ccFeeData  = "=\$D$j*" . $this->creditCard;
			}

			// Store data
			$row    = array(
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
		$data   = array();

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

			$data[$d]['cc_fee'] = $data[$d]['credit'] * ($b_fee->setting / 100);
			$data[$d]['total']  = $data[$d]['cash'] + $data[$d]['credit'] + $data[$d]['layaway'];
		}

		$fees = array(
			'bazaar' => $b_fee->setting,
			'credit' => $cc_fee->setting
		);

		return view('chair.reports.invoice', compact('vendor', 'bazaar', 'fees', 'data'));
	}
}
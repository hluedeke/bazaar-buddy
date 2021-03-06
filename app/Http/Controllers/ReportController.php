<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\AppSettings;
use App\Bazaar;
use App\Money;
use App\SalesType;
use App\CurrentVendor;


use App\Validated;
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
        $this->fee = AppSettings::fee();
        $this->creditCard = AppSettings::creditCardFee();
    }

    /**
     * Display the main page
     *
     * @return view
     * @author Hannah
     */
    public function index()
    {
        $bazaar = Bazaar::whereId($this->current_bazaar)->first();
        $print = null;    // Displays a print button based on the date for quick printing

        if (Carbon::now()->between($bazaar->start_date, $bazaar->end_date->subDay())) {
            $print = 'daily';
        } else if (Carbon::now()->isSameDay($bazaar->end_date) || $bazaar->end_date->isPast()) {
            $print = 'invoice';
        }

        return view('chair.reports.index', compact('bazaar', 'print'));
    }

    /**
     * Acts as the main controller to determine where actions go based on user query.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function report(Request $request)
    {
        if ($request->has('print_daily')) {
            $date = Carbon::now();
            if ($request->has('vendor')) {
                $vendor_number = CurrentVendor::numberFromString($request->input('vendor'));
                return $this->daily($request, $vendor_number, $date);
            }
            return $this->daily($request, null, $date);
        } else if ($request->has('print_invoice')) {
            if ($request->has('vendor')) {
                $vendor_number = CurrentVendor::numberFromString($request->input('vendor'));
                return redirect(action('ReportController@invoice', ['id' => $vendor_number]));
            }
            return redirect(action('ReportController@invoice'));
        } else if ($request->has('vendor')) {
            $vendor_number = CurrentVendor::numberFromString($request->input('vendor'));
            return redirect(action('ReportController@vendor', ['id' => $vendor_number]));
        } else {
            return redirect(action('ReportController@rollup'));
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function rollup(Request $request)
    {
        $bazaar = Bazaar::whereId($this->current_bazaar)->with('vendorsByCheckout')->first();
        $data = array();

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

        $ccFee = $this->creditCard * 100;
        $fee = $this->fee * 100;

        // Checked out column
        $checked_out = array();
        foreach ($bazaar->vendors as $vendor) {
            if ($vendor->pivot->checked_out) {
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
            'checked_out' => $checked_out,
        );

        if (isset($warning))
            $params['warning'] = $warning;

        return view('chair.reports.rollup', $params);
    }

    /**
     * @param Request $request
     * @return int
     */
    public function checkout(Request $request)
    {
        $id = $request->get('id');
        $checked = $request->get('checked') === 'true' ? true : false;

        $v = Vendor::join('bazaar_vendor', 'bazaar_vendor.vendor_id', '=', 'vendors.id')
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
     * @param $id
     * @return view with vendor information
     * @author Hannah
     */
    public function vendor(Request $request, $id = null)
    {
        if ($id !== null) {
            $vendor = CurrentVendor::whereVendorNumber($id)->first();
        } else if ($request->old('vendor') !== null) {
            $vendor = CurrentVendor::fromString($request->old('vendor'));
        } else {
            return redirect(action('ReportController@rollup'));
        }

        // Date report
        $bazaar = Bazaar::find($this->current_bazaar);
        $dates = array();
        $id = 0;

        for ($date = $bazaar->start_date; $date->lte($bazaar->end_date); $date->addDay()) {
            $row = [
                SalesType::CASH => 0,
                SalesType::CARD => 0,
                SalesType::LAYAWAY => 0,
                'total' => 0,
                'sheets' => array(),
                'status' => 'yes'
            ];
            foreach ($vendor->salesSheets() as $sheet) {
                if ($sheet->date_of_sales->isSameDay($date)) {
                    $status = $sheet->getValidationStatus();
                    if($status != Validated::CORRECT)
                        $row['status'] = 'no';
                    $row['sheets'][$sheet->sheet_number] = [
                        SalesType::CASH => $sheet->cash(),
                        SalesType::CARD => $sheet->credit(),
                        SalesType::LAYAWAY => $sheet->layaway(),
                        'total' => $sheet->totalSales(),
                        'status' => $status
                    ];

                    $row[SalesType::CASH] = $sheet->cash();
                    $row[SalesType::CARD] = $sheet->credit();
                    $row[SalesType::LAYAWAY] = $sheet->layaway();
                    $row['total'] =
                        $row[SalesType::CASH] +
                        $row[SalesType::CARD] +
                        $row[SalesType::LAYAWAY];
                    $row[SalesType::CASH] = $row[SalesType::CASH];
                    $row[SalesType::CARD] = $row[SalesType::CARD];
                    $row[SalesType::LAYAWAY] = $row[SalesType::LAYAWAY];
                }
            }

            $id++;
            $row['id'] = "date-$id";
            $row['day'] = $date->format('l');
            $row['daily-date'] = $date->format('m-d-Y');
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
        $data = array();
        $totals = array(
            'Vendor Number' => '',
            SalesType::CASH => 0,
            SalesType::CARD => 0,
            SalesType::LAYAWAY => 0,
            'Total' => 0,
            'Valid' => 'yes'
        );

        // Create data for spreadsheet
        foreach ($bazaar->vendors as $i => $vendor) {

            $row = array();

            $id = $vendor->vendorNumber($bazaar);
            $row['id'] = $id;
            $row['Vendor Number'] = $id;
            $row[SalesType::CASH] = 0;
            $row[SalesType::CARD] = 0;
            $row[SalesType::LAYAWAY] = 0;
            if ($vendor->isValidated($date)) {
                $row['Valid'] = 'yes';
            } else {
                $row['Valid'] = 'no';
                $totals['Valid'] = 'no';
            }

            $row[SalesType::CASH] = $vendor->totalCashSales($bazaar->id, $date);
            $row[SalesType::CARD] = $vendor->totalCardSales($bazaar->id, $date);
            $row[SalesType::LAYAWAY] = $vendor->totalLayawaySales($bazaar->id, $date);

            $totals[SalesType::CASH] += $row[SalesType::CASH];
            $totals[SalesType::CARD] += $row[SalesType::CARD];
            $totals[SalesType::LAYAWAY] += $row[SalesType::LAYAWAY];

            // Calculate our "totals" column, based on format
            if ($view) {
                $row['Total'] =
                    $row[SalesType::CASH] +
                    $row[SalesType::CARD] +
                    $row[SalesType::LAYAWAY];
                $totals['Total'] += $row['Total'];

                // Format
                $row[SalesType::CASH] = $row[SalesType::CASH];
                $row[SalesType::CARD] = $row[SalesType::CARD];
                $row[SalesType::LAYAWAY] = $row[SalesType::LAYAWAY];
                $row['Total'] = $row['Total'];

            } else {
                $j = $i + 2;
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
        $fee = $this->fee * 100;
        $ccFee = $this->creditCard * 100;
        $data = array();
        $totals_row = array(
            'Checkout Order' => 'TOTALS',
            'Vendor Number' => '',
            'Vendor Name' => '',
            'Total Cash Sales' => 0,
            'Total Credit Card Sales' => 0,
            "Credit Card Fee ($ccFee%)" => 0,
            'Total Layaway Sales' => 0,
            'Total Sales' => 0,
            "BBB Fee ($fee%)" => 0,
            'Payment Currency' => '',
            'Valid' => ''
        );

        foreach ($bazaar->vendorsByCheckout as $i => $vendor) {

            $totals = array();

            $totals[SalesType::CASH] = $vendor->totalCashSales($bazaar->id);
            $totals[SalesType::CARD] = $vendor->totalCardSales($bazaar->id);
            $totals[SalesType::LAYAWAY] = $vendor->totalLayawaySales($bazaar->id);


            if ($view) {
                // Calculate totals, fees
                $grandTotal = $totals[SalesType::CASH] + $totals[SalesType::CARD] + $totals[SalesType::LAYAWAY];
                $feeData = round($this->fee * $grandTotal, 2);
                $ccFeeData = round($totals[SalesType::CARD] * $this->creditCard, 2);

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
                $grandTotal = "=SUM(\$D$j,\$E$j,\$G$j)";
                $feeData = "=\$H$j*" . $this->fee;
                $ccFeeData = "=\$E$j*" . $this->creditCard;
            }

            // Store data
            $row = array(
                'Checkout Order' => $vendor->checkout($bazaar),
                'Vendor Number' => $vendor->vendorNumber($bazaar),
                'Vendor Name' => $vendor->name,
                'Total Cash Sales' => $totals[SalesType::CASH],
                'Total Credit Card Sales' => $totals[SalesType::CARD],
                "Credit Card Fee ($ccFee%)" => $ccFeeData,
                'Total Layaway Sales' => $totals[SalesType::LAYAWAY],
                'Total Sales' => $grandTotal,
                "BBB Fee ($fee%)" => $feeData,
                'Payment Currency' => $vendor->payment,
                'Valid' => $vendor->isValidated() ? 'yes' : 'no',
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
        $i = 0;

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
        $vendor = CurrentVendor::whereVendorNumber($id)->firstOrFail();
        $bazaar = Bazaar::find($this->current_bazaar);
        $data = array();
        $j = 0;
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
            $cash = "";
            $credit = "";
            $layaway = "";
            ++$i;

            $m = 1;
            foreach ($data['By Sales Sheet'] as $k => $sheet) {
                $l = $k + 2;
                if (Carbon::createFromFormat('m/d/Y', $sheet['Date'])->isSameDay($date)) {
                    ++$m;
                    $cash .= "'By Sales Sheet'!C$l,";
                    $credit .= "'By Sales Sheet'!D$l,";
                    $layaway .= "'By Sales Sheet'!E$l,";

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

    public function invoice(Request $request, $id = null)
    {
        if ($id !== null) {
            $vendors = array(CurrentVendor::whereVendorNumber($id)->firstOrFail());
        } else if ($request->has('vendor_string')) {
            $vendors = array(CurrentVendor::fromString($request->input('vendor_string')));
        } else {
            $vendors = CurrentVendor::all();
        }
        $bazaar = Bazaar::find($this->current_bazaar);
        $cc_fee = AppSettings::whereName('credit_card_fee')->first();
        $b_fee = AppSettings::whereName('bazaar_fee')->first();
        $cc_fee = $cc_fee->setting;
        $b_fee = $b_fee->setting;
        $data = array();
        $totals = array();

        foreach ($vendors as $vendor) {
            for ($date = $bazaar->start_date; $date->lte($bazaar->end_date); $date->addDay()) {
                $d = $date->format('j-M-Y');

                $data[$vendor->id][$d] = [
                    'cash' => 0,
                    'credit' => 0,
                    'layaway' => 0,
                ];

                // Go through each sheet by date and add to daily total
                foreach ($vendor->salesSheets() as $sheet) {
                    if ($sheet->date_of_sales->isSameDay($date)) {
                        $data[$vendor->id][$d]['cash'] += $sheet->cash();
                        $data[$vendor->id][$d]['credit'] += $sheet->credit();
                        $data[$vendor->id][$d]['layaway'] += $sheet->layaway();
                    }
                }

                $data[$vendor->id][$d]['cc_fee'] = $data[$vendor->id][$d]['credit'] * ($cc_fee / 100);
                $data[$vendor->id][$d]['total'] = $data[$vendor->id][$d]['cash'] + $data[$vendor->id][$d]['credit'] + $data[$vendor->id][$d]['layaway'];

                $cc_fee_total = $vendor->credit() * ($cc_fee / 100);
                $b_fee_total = $vendor->totalSales() * ($b_fee / 100);
                $owedTotal = $vendor->totalSales() - $vendor->layaway();    // The amount owed to vendor does not include layaway
                $deduct = $cc_fee_total + $b_fee_total + $vendor->table_fee + $vendor->audit_adjust;

                // Totals
                $totals[$vendor->id]['cash'] = number_format($vendor->cash(), 2);
                $totals[$vendor->id]['credit'] = number_format($vendor->credit(), 2);
                $totals[$vendor->id]['layaway'] = number_format($vendor->layaway(), 2);
                $totals[$vendor->id]['total'] = number_format($vendor->totalSales(), 2);
                $totals[$vendor->id]['cc_fee'] = number_format($cc_fee_total, 2);
                $totals[$vendor->id]['b_fee'] = number_format($b_fee_total, 2);
                $totals[$vendor->id]['deduct'] = number_format($deduct, 2);
                $totals[$vendor->id]['owed'] = number_format($owedTotal - $deduct, 2);
                $totals[$vendor->id]['table_fee'] = number_format($vendor->table_fee, 2);
                $totals[$vendor->id]['audit_adjust'] = number_format($vendor->audit_adjust, 2);

                // Format for pretty print
                $data[$vendor->id][$d]['cash'] = number_format($data[$vendor->id][$d]['cash'], 2);
                $data[$vendor->id][$d]['credit'] = number_format($data[$vendor->id][$d]['credit'], 2);
                $data[$vendor->id][$d]['layaway'] = number_format($data[$vendor->id][$d]['layaway'], 2);
                $data[$vendor->id][$d]['cc_fee'] = number_format($data[$vendor->id][$d]['cc_fee'], 2);
                $data[$vendor->id][$d]['total'] = number_format($data[$vendor->id][$d]['total'], 2);
            }

        }

        $fees = array(
            'bazaar' => $b_fee,
            'credit' => $cc_fee
        );

        return view('chair.reports.invoice', compact('vendors', 'bazaar', 'fees', 'data', 'totals', 'organization'));
    }

    public function daily(Request $request, $id = null, $date = null)
    {
        if (!$date) {
            $date = Carbon::createFromFormat('m-d-Y', $request->input('date'));
        }
        $bazaar = Bazaar::find($this->current_bazaar);
        $totals = array();

        if ($id !== null) {
            $vendors = array(CurrentVendor::whereVendorNumber($id)->firstOrFail());
        } else if ($request->has('vendor_number')) {
            $vendors = array(CurrentVendor::whereVendorNumber($request->input('vendor_number'))->firstOrFail());
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

            $totals[$vendor->id]['cash'] = number_format($totals[$vendor->id]['cash'], 2);
            $totals[$vendor->id]['credit'] = number_format($totals[$vendor->id]['credit'], 2);
            $totals[$vendor->id]['layaway'] = number_format($totals[$vendor->id]['layaway'], 2);
            $totals[$vendor->id]['total'] = number_format($totals[$vendor->id]['total'], 2);

            if ($date->isSameDay($bazaar->start_date) && $vendor->table_fee != 0) {
                $totals[$vendor->id]['table_fee'] = number_format($vendor->table_fee, 2);
            }
        }
        return view('chair.reports.daily', compact('date', 'vendors', 'totals', 'bazaar'));
    }
}
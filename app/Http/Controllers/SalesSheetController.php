<?php namespace App\Http\Controllers;

use App;
use Auth;

use App\Http\Requests;

use Illuminate\Http\Response;
use Illuminate\Http\Request;

use Redirect;
use Session;
use Validator;

use App\AppSettings;
use App\CurrentVendor;
use App\Sale;
use App\SalesSheet;
use App\SalesType;
use App\Validated;

use Carbon\Carbon;

class SalesSheetController extends Controller
{

	protected $current_bazaar;

	/**
	 * Constructor
	 *
	 * @author Hannah
	 */
	public function __construct()
	{
		$settings             = AppSettings::whereName('current_bazaar')->firstOrFail();
		$this->current_bazaar = $settings->setting;
		$this->middleware('vol.auth');
	}

	/**
	 * The main index page for the sales sheet area
	 *
	 * @return Response
	 * @author Hannah
	 */
	public function index()
	{
		$salesSheets = SalesSheet::whereCreatedBy(Auth::user()->id)->whereBazaarId($this->current_bazaar)
			->orderBy('updated_at', 'DESC')->get();
		$validated = SalesSheet::whereValidatedBy(Auth::user()->id)->whereBazaarId($this->current_bazaar)->get();

		return view('main\sales_sheet\home', ['salesSheets' => $salesSheets, 'validated' => $validated]);
	}

	/**
	 * undocumented function
	 *
	 * @param id
	 * @return Response
	 * @author Hannah
	 */
	public function show($id)
	{
		$sheet = SalesSheet::where('sheet_number', '=', $id)->whereBazaarId($this->current_bazaar)->firstOrFail();
		return view('main\sales_sheet\show', compact('sheet'));
	}

	/**
	 * Returns the input sheet info view
	 *
	 * @return Response
	 * @author Hannah
	 */
	public function createInfo()
	{
		$sheet = new SalesSheet;
		$sheet->vendor()->associate(new CurrentVendor);
		$curr_date = Carbon::now()->format('m/d/Y');

		if (Session::get('sheet') != null) {
			$sheet     = Session::get('sheet');
			$curr_date = $sheet->date_of_sales->format('m/d/Y');
		}

		return view('main\sales_sheet\create_info', ['sheet' => $sheet, 'curr_date' => $curr_date]);
	}

	/**
	 * Stores the basic sales sheet information to a session variable
	 *
	 * @param Request $request
	 * @return Response
	 * @author Hannah
	 */
	public function storeInfo(Request $request)
	{

		// Get the vendor input and grab just the number
		if ($request->exists('vendor')) {
			$vendor = $request->input('vendor');
			if ($vendor != '') {
				$vendorArr = explode('-', $vendor);
				if (count($vendorArr) >= 2) {
					$request->merge(['vendor_number' => trim($vendorArr[0])]);
				} else if(is_numeric($vendor)) {
					$request->merge(['vendor_number' => $vendor]);
				} else {
					$vendor = CurrentVendor::where('name', '=', $vendor)->firstOrFail();
					$request->merge(['vendor_number' => $vendor->vendor_number]);
				}
			}
		}

		// Validation!
		$this->validateSheetInfo($request, $this->current_bazaar);

		// Check for between dates
		$sale_date = Carbon::createFromFormat('m/d/Y', $request->input('date_of_sales'));
		$bazaar = App\Bazaar::findOrFail($this->current_bazaar);

		if(!$sale_date->between($bazaar->start_date, $bazaar->end_date)) {
			return redirect()->back()->withErrors('The date has to be during the bazaar.');
		}

		$sheet = new SalesSheet([
			'date_of_sales' => $request->input('date_of_sales'),
			'sheet_number' => $request->input('sheet_number')
		]);

		$vendor = CurrentVendor::where('vendor_number', '=', $request->input('vendor_number'))->first();
		$sheet->vendor()->associate($vendor);

		Session::set('sheet', $sheet);
		return Redirect::to(action('SalesSheetController@createSales'));
	}

	/**
	 * Returns the sales input view if the sales sheet has been entered,
	 * otherwise returns the sales sheet input view
	 *
	 * @return Response
	 * @author Hannah
	 */
	public function createSales()
	{

		$sheet = Session::get('sheet');
		$sales = Session::get('sheet_sales');

		// Verify that the user has actually put in data for the sheet info
		if (isset($sheet->sheet_number)) {

			// Any pre-defined cash sales
			$cash_sales    = array();
			$credit_sales  = array();
			$layaway_sales = array();

			// If our form has failed validation
			if (Session::has('_old_input')) {
				foreach (Session::get('_old_input')['cash_amount'] as $amount) {
					$cash_sales[] = new Sale(['amount' => $amount]);
				}
				foreach (Session::get('_old_input')['card_amount'] as $i => $amount) {
					$credit_sales[] = new Sale([
						'amount' => $amount,
						'receipt_number' => Session::get('_old_input')['receipt_number'][$i],
					]);
				}
				foreach (Session::get('_old_input')['layaway_amount'] as $amount) {
					$layaway_sales[] = new Sale(['amount' => $amount]);
				}
			} else {
				if ($sales != null)
					foreach ($sales as $sale) {
						if ($sale->sales_type == SalesType::CASH)
							$cash_sales[] = $sale;
						if ($sale->sales_type == SalesType::CARD)
							$credit_sales[] = $sale;
						if ($sale->sales_type == SalesType::LAYAWAY)
							$layaway_sales[] = $sale;
					};
			}

			return view('main\sales_sheet\create_sales', [
				'sheet' => $sheet,
				'pending_id' => Validated::PENDING,
				'cash_sales' => $cash_sales,
				'credit_sales' => $credit_sales,
				'layaway_sales' => $layaway_sales
			]);
		}
		return Redirect::to(action('SalesSheetController@createInfo'));
	}

	/**
	 * Stores the sales information temporarily to the session
	 *
	 * @param Request $request
	 * @return Response
	 * @author Hannah
	 */
	public function storeSales(Request $request)
	{
		$sheet = Session::get('sheet');
		$sales = array();

		// Validation!
		$v = $this->validateSheetSales($request);
		if ($v->fails()) {
			return redirect()->back()->withErrors($v->errors())->with('_old_input', $request->all());
		}

		// Format cash inputs
		foreach ($request['cash_amount'] as $amount) {
			if ($amount != '') {
				$sale = new Sale([
					'sales_type' => SalesType::CASH,
					'validated' => Validated::CORRECT,
					'amount' => $amount
				]);

				$sale->sales_sheet()->associate($sheet);
				$sales[] = $sale;
			}
		}

		// Format card inputs
		for ($i = 0; $i < count($request['card_amount']); ++$i) {
			if ($request['card_amount'][$i] != '') {
				$sale = new Sale([
					'sales_type' => SalesType::CARD,
					'validated' => Validated::PENDING,
					'receipt_number' => $request['receipt_number'][$i],
					'amount' => $request['card_amount'][$i]
				]);

				// Get rid of ugly zeroes
				$sale->terminal_id = ($sale->terminal_id == 0) ? NULL : $sale->terminal_id;
				$sale->sequence_id = ($sale->sequence_id == 0) ? NULL : $sale->sequence_id;

				$sale->sales_sheet()->associate($sheet);
				$sales[] = $sale;
			}
		}

		// Format layaway inputs
		foreach ($request['layaway_amount'] as $amount) {
			if ($amount != '') {
				$sale = new Sale([
					'sales_type' => SalesType::LAYAWAY,
					'validated' => Validated::LAYAWAY,
					'amount' => $amount
				]);

				$sale->sales_sheet()->associate($sheet);
				$sales[] = $sale;
			}
		}

		// Store info to session and move on
		Session::set('sheet_sales', $sales);
		return Redirect::to(action('SalesSheetController@finalize'));

	}

	/**
	 * undocumented function
	 *
	 * @return Response
	 * @author Hannah
	 */
	public function finalize()
	{
		if (Session::get('sheet') !== null && Session::get('sheet_sales') != null) {
			$sheet = Session::get('sheet');
			$sales = Session::get('sheet_sales');

			return view('main\sales_sheet\finalize', ['sheet' => $sheet, 'sales' => $sales]);
		} else {
			return Redirect::to(action('SalesSheetController@createSales'));
		}
	}

	/**
	 * undocumented function
	 *
	 * @return Response
	 * @author Hannah
	 */
	public function storeSalesSheet()
	{
		$sheet = Session::get('sheet');
		$sales = array();
		if(Session::has('sheet_sales')) {
			$sales = Session::get('sheet_sales');
		}

		// If there are no credit card sales, we need to skip the validation step
		$needsValidation = false;
		foreach ($sales as $sale) {
			if ($sale->sales_type == SalesType::CARD)
				$needsValidation = true;
		}
		if (!$needsValidation)
			$sheet->validated_by = Auth::user()->id;

		// TODO VALIDATION!

		$sheet->bazaar_id  = $this->current_bazaar;
		$sheet->created_by = Auth::user()->id;
		$sheet->save();
		$sheet->sales()->saveMany($sales);

		Session::forget('sheet');
		Session::forget('sheet_sales');

		return Redirect::to(action('MainController@index'));
	}

	/**
	 * Destroy any session data for sales sheet inputs and return the main screen
	 *
	 * @return Response
	 * @author Hannah
	 */
	public function destroySession()
	{
		Session::forget('sheet');
		Session::forget('sheet_sales');

		return Redirect::to(action('MainController@index'));
	}

	/**
	 * Displays the edit page for a singular sales sheet
	 *
	 * @return Respnose
	 * @author Hannah
	 */
	public function edit($id)
	{
		$sheet = SalesSheet::whereSheetNumber($id)->firstOrFail();
		return view('main\sales_sheet\edit', ['sheet' => $sheet]);
	}

	/**
	 * Validates the input sheet info form data
	 *
	 * @param Request $request
	 * @param string $current_bazaar
	 * @return void
	 * @author Hannah
	 */
	private function validateSheetInfo(Request $request, $current_bazaar)
	{
		$this->validate($request, [
			'date_of_sales' => 'required|date',
			'sheet_number' => [
				'required',
				'numeric',
				"unique:sales_sheets,sheet_number,
										NULL,id,bazaar_id,$current_bazaar",
			],
			'vendor_number' => 'required|exists:current_vendors,vendor_number',
		], [
			'vendor_number.exists' => 'This vendor does not exist. Please enter a current vendor.',
		]);
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Hannah
	 */
	private function  validateSheetSales(Request $request)
	{
		$v = Validator::make($request->all(), [
			'cash_amount' => 'array',
			'card_amount' => 'array',
			//'receipt_number' 	=> 'array'
		], [
			'dollar_format' => 'All cash and card amounts must be in dollars.',
			'receipt_number.numeric' => 'All sheet numbers must be numbers.',
			//'unique'	=> 'One of your receipts is a duplicate.'
		]);

		$v->each('cash_amount', ['dollar_format']);
		$v->each('card_amount', ['dollar_format']);
		//$v->each('receipt_number', ['numeric', 'unique:sales,receipt_number']);

		return $v;
	}
}

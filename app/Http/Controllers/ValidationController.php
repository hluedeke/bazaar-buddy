<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Sale;
use App\SalesSheet;
use App\SalesType;
use App\Validated;
use App\Validation;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class ValidationController extends Controller
{

	/**
	 * Constructor
	 *
	 * @author Hannah
	 */
	public function __construct()
	{
		$this->middleware('auth');
	}

	/**
	 * undocumented function
	 *
	 * @param int $id
	 * @return \Response
	 * @author Hannah
	 */
	public function show($id)
	{
		$sheet = SalesSheet::where('sheet_number', '=', $id)->firstOrFail();
		return view('main\sales_sheet\validate\show', compact('sheet'));
	}

	/**
	 * Select a sales sheet to validate
	 *
	 * @param Request $request
	 * @return \Response
	 * @author Hannah
	 */
	public function select(Request $request)
	{
		Session::forget('sales');

		if ($request->has('sheet_number')) {
			$sheetNumber = explode(' ', $request->input('sheet_number'));
			$request->merge(['sheet_numeric' => $sheetNumber[0]]);

			// Validation
			return $this->validateSheetNumber($request, $sheetNumber[0]);
		}
		return view('main\sales_sheet\validate\select');
	}

	/**
	 * Displays the validation page
	 *
	 * @return \Response
	 * @author Hannah
	 */
	public function validateSheet()
	{
		$sheet = SalesSheet::where('sheet_number', '=', session('sheet_number'))->firstOrFail();
		$old   = Session::get('_old_input');

		if (Session::has('sales')) {
			$sales = Session::get('sales');
		} else {
			$sales = $sheet->sales()->whereSalesType(SalesType::CARD)->get();
		}

		// For checkboxes
		if(Session::has('_old_input')) {
			foreach ($sales as $sale) {
				if (isset($old['status'][$sale->id]))
					$sale->validated = $old['status'][$sale->id];
			}
		}


		return view('main\sales_sheet\validate\validate', array(
			'sheet' => $sheet,
			'sales' => $sales,
			'old' => $old,
		));
	}

	/**
	 * Stores information from the validation page (without saving to database)
	 *
	 * @param Request $request
	 * @author Hannah
	 */
	public function store(Request $request)
	{
		$sales = array();

		if ($request->has('amount'))
			$list_size = sizeof($request->input('amount'));
		else
			$list_size = 0;

		// Validation
		$v = Validator::make($request->all(), [
			'receipt_num' => 'array',
			'amount' => 'array',
			'term_id' => 'array',
			'seq_num' => 'array',
			'status' => "array|required|array_size:$list_size",
		], [
			'dollar_format' => 'All sale amounts must be in dollars.',
			'receipt_num.numeric' => 'One of your receipt numbers isn\'t a number.',
			'status.in' => 'All sales need to be either correct, incorrect, or missing',
			'status.array_size' => 'All sales need to be either correct, incorrect, or missing',
			'status.required' => 'All sales need to be either correct, incorrect, or missing',
		]);

		$v->each('amount', ['dollar_format', 'required']);
		$v->each('receipt_num', ['numeric']);
		$v->each('status', ['in:' . Validated::CORRECT . ',' . Validated::INCORRECT . ',' . Validated::MISSING]);
		$v->each('term_id', ['numeric']);
		$v->each('seq_num', ['numeric']);

		if ($v->fails()) {
			return redirect()->back()->withErrors($v->errors())->with('_old_input', $request->all());
		}

		foreach ($request->input('receipt_num') as $id => $receipt_num) {
			$sale                 = new Sale;
			$sale->id             = $id;
			$sale->receipt_number = $receipt_num;
			$sale->amount         = $request->input('amount')[$id];
			$sale->terminal_id    = $request->input('term_id')[$id];
			$sale->sequence_id    = $request->input('seq_num')[$id];
			$sale->validated      = $request->input('status')[$id];

			$sales[$id] = $sale;
		}

		//die(var_dump(Input::all()));
		Session::set('sales', $sales);
		return Redirect::to(action('ValidationController@finalize'));
	}

	/**
	 * undocumented function
	 *
	 * @return \Response
	 * @author Hannah
	 */
	public function finalize()
	{
		if (!Session::has('sales') || !Session::has('sheet_number'))
			return Redirect::to(action('ValidationController@select'));

		$sheet  = SalesSheet::where('sheet_number', '=', session('sheet_number'))->firstOrFail();
		$sales  = Session::get('sales');
		$failed = FALSE;

		// Get new total
		$cc_total = 0;
		foreach($sales as $sale) {
			$cc_total += $sale->amount;
		}
		$sheet_total = $sheet->cash() + $sheet->layaway() + $cc_total;

		return view('main\sales_sheet\validate\finalize', array(
			'sheet' => $sheet,
			'failed' => $failed,
			'sales' => $sales,
			'cc_total' => "$".number_format($cc_total, 2),
			'sheet_total' => "$".number_format($sheet_total,2),
		));
	}

	/**
	 * Saves all of the validations the user has input
	 *
	 * @return \Response
	 * @author Hannah
	 */
	public function save()
	{
		if (!Session::has('sales') || !Session::has('sheet_number'))
			return Redirect::to(action('ValidationController@select'));

		foreach (Session::get('sales') as $id => $input_sale) {
			$sale                 = Sale::findOrFail($id);
			$sale->receipt_number = $input_sale->receipt_number;
			$sale->amount         = $input_sale->amount;
			$sale->terminal_id    = $input_sale->terminal_id;
			$sale->sequence_id    = $input_sale->sequence_id;
			$sale->validated      = $input_sale->validated;
			$sale->save();
		}

		$sheet               = SalesSheet::where('sheet_number', '=', session('sheet_number'))->firstOrFail();
		$sheet->validated_by = Auth::user()->id;
		$sheet->save();

		return $this->destroySession();
	}

	/**
	 * undocumented function
	 *
	 * @return \Response
	 * @author Hannah
	 */
	public function destroySession()
	{
		Session::forget('sales');

		return Redirect::to(action('MainController@index'));
	}

	/**
	 * Runs validation on our user's input for sheet number, checking if the sheet exists,
	 * if it's already been validated, etc.
	 *
	 * @return \Response
	 * @author Hannah
	 */
	private function validateSheetNumber($request, $sheetNumber)
	{
		// Validation
		$v = Validator::make($request->all(), [
			'sheet_numeric' => 'numeric|exists:sales_sheets,sheet_number',
			'sheet_number' => 'exists:sales_sheets,sheet_number,validated_by,NULL',
		], [
			'sheet_numeric.numeric' => 'The sheet number must be a number.',
			'sheet_numeric.exists' => 'This sheet does not exist.',
			'sheet_number.exists' => 'This sheet has already been validated!',
		]);

		if ($v->passes()) {
			$v2 = Validator::make($request->all(), [
				'sheet_numeric' => 'exists:sales_sheets,sheet_number',
			], [
				'sheet_numeric.exists' => 'This sheet has already been validated.',
			]);

			if ($v2->passes()) {
				Session::set('sheet_number', $sheetNumber);
				return redirect(action('ValidationController@validateSheet'));
			} else {
				return redirect()->back()->withErrors($v2->errors());
			}
		}
		return redirect()->back()->withErrors($v->errors());
	}

}

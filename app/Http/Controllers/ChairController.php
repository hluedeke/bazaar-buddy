<?php namespace App\Http\Controllers;

use App;
use Auth;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

use Illuminate\Database\Eloquent\ModelNotFoundException;

use App\SalesSheet;
use App\Validated;
use App\CurrentVendor;
use App\Sale;
use App\SalesType;
use App\AppSettings;

class ChairController extends Controller
{

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Hannah
	 */
	public function __construct()
	{
		$settings             = AppSettings::whereName('current_bazaar')->firstOrFail();
		$this->current_bazaar = $settings->setting;
		$this->middleware('auth');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$allSheets = SalesSheet::whereBazaarId($this->current_bazaar)->orderBy('updated_at')->get();
		$sheets    = array();

		// Add only the sheets that need chair review to this array for the view
		foreach ($allSheets as $sheet) {
			if ($sheet->getValidationStatus() != Validated::CORRECT && $sheet->getValidationStatus() != Validated::PENDING)
				$sheets[] = $sheet;
		}
		return view('chair/home', ['sheets' => $sheets]);
	}

	/**
	 * Views a single sales sheet to review.
	 *
	 * @return the view
	 * @author Hannah
	 */
	public function review($id)
	{
		try {
			$sheet = SalesSheet::whereSheetNumber($id)->firstOrFail();
			return view('chair/review', ['sheet' => $sheet]);
		} catch (ModelNotFoundException $e) {
			return Redirect::to(action('ChairController@index'));
		}
	}

	/**
	 * @param $id
	 * @param Request $request
	 * @return mixed
	 */
	public function update($id, Request $request)
	{
		try {
			$sheet = SalesSheet::whereSheetNumber($id)->firstOrFail();
		} catch (ModelNotFoundException $e) {
			Session::flash('error', "Sheet $id does not exist in the database.");
			return Redirect::to(action('ChairController@index'));
		}

		// Validation HERE

		// Get the vendor to save
		if ($request->exists('vendor')) {
			$vendor = $request->input('vendor');
			if ($vendor != '') {
				$vendor = CurrentVendor::fromString($vendor);
				$sheet->vendor()->associate($vendor);
			}
		}

		// Sheet data
		$sheet->date_of_sales = $request->input('date_of_sales');
		$sheet->sheet_number  = $request->input('sheet_number');
		$sheet->validated_by  = Auth::user()->id;
		$sheet->save();

		// Remove any deleted sales
		foreach ($sheet->sales as $sale) {
			if (!in_array($sale->id, $request->input('sales'))) {
				$sale->delete();
			}
		}

		// Sales
		foreach ($request->input('sales') as $i => $sales_id) {
			try {
				$sale = Sale::findOrFail($sales_id);

				$sale->sales_type = $request->input('sales_type')[$i];
				$sale->amount     = $request->input('amount')[$i];

				// Card specific data		
				if ($sale->sales_type == SalesType::CARD) {
					$sale->receipt_number = $request->input('receipt_number')[$i];
					$sale->terminal_id    = $request->input('term_id')[$i];
					$sale->sequence_id    = $request->input('seq_num')[$i];
				}

				if (isset($request->input('valid')[$i])) {
					if ($sale->validated != Validated::CORRECT)
						$sale->validated = Validated::CH_CORRECTED;
				}
				$sale->save();

			} catch (ModelNotFoundException $e) {
				$sale = new Sale;

				$sale->sales_type = $request->input('sales_type')[$i];
				$sale->amount     = $request->input('amount')[$i];

				// Card specific data		
				if ($sale->sales_type == SalesType::CARD) {
					$sale->receipt_number = $request->input('receipt_number')[$i];
					$sale->terminal_id    = $request->input('term_id')[$i];
					$sale->sequence_id    = $request->input('seq_num')[$i];
				}

				if (isset($request->input('valid')[$i]) || $sale->sales_type == SalesType::CASH) {
					$sale->validated = Validated::CORRECT;
				} else if ($sale->sales_type == SalesType::LAYAWAY)
					$sale->validated = Validated::LAYAWAY;
				else
					$sale->validated = Validated::INCORRECT;

				$sheet->sales()->save($sale);
			}
		}

		Session::flash('message', "Sheet $id has been updated successfully.");
		return Redirect::to(action('ChairController@review', ['id' => $id]));
	}

	/**
	 * @param Request $request
	 * @return \Illuminate\Validation\Validator
	 */
	private function validateSheetSales(Request $request)
	{
		\Validator::extend('dollar_format', function ($attribute, $value, $parameters) {
			$test = preg_replace("/([^0-9\\.])/i", "", $value);
			return is_numeric($test) && !is_null($test);
		});

		$v = \Validator::make($request->all(), [
			'date_of_sales' => 'date|required',
			'sheet_number' => 'numeric|required',
			'vendor' => 'required|exists:current_vendors,name',
			'receipt_number' => 'array',
			'amount' => 'array',
			'term_id' => 'array',
			'seq_num' => 'array',
			'sales_type' => 'array',
			'valid' => 'array',
		], [
			'dollar_format' => 'All cash and card amounts must be in dollars.',
			'receipt_number.numeric' => 'All sheet numbers must be numbers.',
			'unique' => 'One of your receipts is a duplicate.'
		]);

		$v->each('amount', ['dollar_format']);
		$v->each('term_id', ['numeric']);
		$v->each('seq_num', ['numeric']);
		$v->each('sales_type', ['in:' . implode(",", SalesType::values())]);
		$v->each('receipt_number', ['numeric', 'unique:sales,receipt_number']);

		return $v;
	}
}

<?php namespace App\Http\Controllers;

use App\AppSettings;
use App\CurrentVendor;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\SalesSheet;
use App\Validated;
use Illuminate\Http\Request;

class SearchController extends Controller
{

	/**
	 *
	 */
	public function __construct()
	{
		$settings             = AppSettings::whereName('current_bazaar')->firstOrFail();
		$this->current_bazaar = $settings->setting;
		$this->middleware('auth');
	}

	/**
	 * Performs the universal search function for the autocomplete
	 *
	 * @param Request $request
	 * @return array
	 */
	public function acSearch(Request $request)
	{

		$query = $request->input('q');
		$data  = array();

		if (!is_numeric($query)) {
			$statuses = Validated::statusSearch($query);
			foreach ($statuses as $status) {
				$data[] = [
					'category' => 'Validation Status',
					'value' => $status,
					'id' => 1
				];
			}

			$vendors = CurrentVendor::where('name', 'LIKE', "%$query%")->get();
			foreach ($vendors as $vendor) {
				$data[] = [
					'category' => 'Vendor',
					'value' => $vendor->name,
					'id' => $vendor->id
				];
			}
		}

		if (is_numeric($query)) {

			$receipt_nums = \DB::table('sales')
				->join('sales_sheets', 'sales.sales_sheet_id', '=', 'sales_sheets.id')
				->where('sales_sheets.bazaar_id', '=', $this->current_bazaar)
				->where('sales.receipt_number', 'LIKE', "$query%")->get();
			$term_ids     = \DB::table('sales')
				->join('sales_sheets', 'sales.sales_sheet_id', '=', 'sales_sheets.id')
				->where('sales_sheets.bazaar_id', '=', $this->current_bazaar)
				->where('sales.terminal_id', 'LIKE', "$query%")
				->orWhere('sequence_id', 'LIKE', "$query%")->get();

			$sheets = SalesSheet::where('sheet_number', 'LIKE', "$query%")->whereBazaarId($this->current_bazaar)->get();

			$vendors = CurrentVendor::where('vendor_number', 'LIKE', "$query%")->get();

			// Populate dropdown with fetched results
			foreach ($sheets as $s) {
				$data[] = [
					'category' => 'Sheet Number',
					'value' => $s->sheet_number,
					'id' => $s->sheet_number
				];
			}
			foreach ($receipt_nums as $r) {
				$data[] = [
					'category' => 'Receipt Number',
					'value' => $r->receipt_number . " (" . $r->sheet_number . ")",
					'id' => $r->sheet_number
				];
			}
			foreach ($term_ids as $t) {
				$data[] = array(
					'category' => 'Terminal ID, Sequence Num',
					'value' => $t->terminal_id . ", " . $t->sequence_id . " (" . $t->sheet_number . ")",
					'id' => $t->sheet_number
				);
			}
			foreach ($vendors as $v) {
				$data[] = array(
					'category' => 'Vendor',
					'value' => $v->vendor_number . " - " . $v->name,
					'id' => $v->id
				);
			}
		}

		return $data;
	}

	/**
	 * @param Request $request
	 * @return \Illuminate\Http\RedirectResponse|string
	 */
	public function search(Request $request)
	{

		switch ($request->input('category')) {
			case 'Sheet Number':
			case 'Receipt Number':
			case 'Terminal ID, Sequence Num (Sheet)':
				return \Redirect::to(action('ChairController@review', ['id' => $request->input('id')]));
			case 'Validation Status':
				return \Redirect::to(action('SearchController@validation', ['status' => $request->input('value')]));
			case 'Vendor':
				return \Redirect::to(action('SearchController@vendor', ['id' => $request->input('id')]));;
				break;
		}
	}

	/**
	 * @param $status
	 * @return \Illuminate\View\View
	 */
	public function validation($status)
	{
		$sheets = SalesSheet::whereBazaarId($this->current_bazaar)->get();

		$sheets = $sheets->filter(function($item) use ($status) {
			return $item->getValidationStatus() == $status;
		});

		return view('search.validation', compact('sheets', 'status'));
	}

	/**
	 * @param $id
	 * @return \Illuminate\View\View
	 */
	public function vendor($id)
	{
		$vendor = CurrentVendor::find($id);
		$sheets = SalesSheet::whereVendorId($id)->get();

		return view('search.vendor', compact('vendor', 'sheets'));
	}

}

<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Sale;
use Illuminate\Http\Request;

use App\CurrentVendor;
use App\SalesSheet;
use App\Validated;
use App\Vendor;

class HelperController extends Controller
{

	public function __construct()
	{
		$this->middleware('auth');
	}

	// Gets a list of vendors for use with autocomplete
	public function acVendor(Request $request)
	{
		$input   = $request->input('input');
		$vendors = CurrentVendor::where('name', 'LIKE', "%$input%")
			->orWhere('vendor_number', 'LIKE', "$input%")->get();
		$acList  = [];

		foreach ($vendors as $vendor) {
			$acList[] = $vendor->vendor_number . " - " . $vendor->name;
		}

		return $acList;
	}

	// Gets a list of vendors for use with autocomplete
	public function acVendorName(Request $request)
	{
		$input   = $request->input('input');
		$vendors = Vendor::where('name', 'LIKE', "%$input%")->get();
		$acList  = [];

		foreach ($vendors as $vendor) {
			$acList[] = $vendor->name;
		}

		return $acList;
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Hannah
	 */
	public function acSalesSheet(Request $request)
	{
		$input  = $request->input('input');
		$sheets = SalesSheet::where('sheet_number', 'LIKE', "$input%")->whereNull('validated_by')->get();
		$acList = [];

		foreach ($sheets as $sheet) {
			$acList[] = "$sheet->sheet_number (" . $sheet->vendor->name . ")";
		}

		return $acList;
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
			foreach($vendors as $vendor) {
				$data[] = [
					'category' => 'Vendor',
					'value' => $vendor->name,
					'id' => 1
				];
			}
		}

		if (is_numeric($query)) {
			$receipt_nums = Sale::where('receipt_number', 'LIKE', "$query%")->get();
			$term_ids     = Sale::where('terminal_id', 'LIKE', "$query%")->orWhere('sequence_id', 'LIKE', "$query%")->get();
			$sheets       = SalesSheet::where('sheet_number', 'LIKE', "$query%")->get();

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
					'value' => $r->receipt_number,
					'id' => $r->sales_sheet->sheet_number
				];
			}
			foreach ($term_ids as $t) {
				$data[] = array(
					'category' => 'Terminal ID, Sequence Num (Sheet)',
					'value' => $t->terminal_id . ", " . $t->sequence_id . " (" . $t->sales_sheet->sheet_number . ")",
					'id' => $t->sales_sheet->sheet_number
				);
			}
		}

		return $data;
	}

	public function search(Request $request)
	{

		switch($request->input('category')) {
			case 'Sheet Number':
			case 'Receipt Number':
			case 'Terminal ID, Sequence Num (Sheet)':
				return \Redirect::to(action('SalesSheetController@show', ['id' => $request->input('id')]));
			case 'Validation Status':
			case 'Vendor':
				return 'TO DO';
				break;
		}
	}
}

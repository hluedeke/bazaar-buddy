<?php namespace App\Http\Controllers;

use App\AppSettings;
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
		$settings             = AppSettings::whereName('current_bazaar')->firstOrFail();
		$this->current_bazaar = $settings->setting;
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

}

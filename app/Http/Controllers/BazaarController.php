<?php namespace App\Http\Controllers;

use App;
use Session;

use App\Http\Requests;

use App\Bazaar;
use App\AppSettings;
use App\Vendor;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Carbon\Carbon;

class BazaarController extends Controller
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
		$this->middleware('auth');
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$bazaars = Bazaar::where('id', '!=', $this->current_bazaar)->get();
		$curr    = Bazaar::find($this->current_bazaar);

		return view('chair.bazaar.index', ['bazaars' => $bazaars, 'curr' => $curr]);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if (Session::has('start_date'))
			$start_date = Session::get('start_date');
		else
			$start_date = Carbon::now()->format('m/d/Y');
		if (Session::has('end_date'))
			$end_date = Session::get('end_date');
		else
			$end_date = Carbon::now()->format('m/d/Y');
		return view('chair.bazaar.create', compact('start_date', 'end_date'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store(Request $request)
	{
		$this->validate($request, [
			'name' => 'required|between:5,100',
			'start_date' => 'required|date|before:end_date',
			'end_date' => 'required|date'
		]);

		$bazaar             = new Bazaar;
		$bazaar->name       = $request->input('name');
		$bazaar->start_date = $request->input('start_date');
		$bazaar->end_date   = $request->input('end_date');
		$bazaar->save();


		return view('chair.bazaar.show', compact('bazaar'));
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int $id
	 * @return Response
	 */
	public function show($id)
	{
		$bazaar = Bazaar::findOrFail($id);
		return view('chair.bazaar.show', compact('bazaar'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}

	/**
	 * Updates the current bazaar
	 *
	 * @return void
	 * @author Hannah
	 */
	public function current($id)
	{
		$setting          = AppSettings::whereName('current_bazaar')->firstOrFail();
		$setting->setting = $id;
		$setting->save();

		return Redirect::back();
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int $id
	 * @return Response
	 */
	public function update($id, Request $request)
	{
		$v = \Validator::make($request->all(), [
			'name' => 'array',
			'checkout' => 'array',
			'currency' => 'array',
			'number' => 'array',
		], [
			'required' => 'Something is missing for one of the vendors. Make sure you have all data for each vendor.',
			'dollar_format' => 'All table fee and audit amounts must be in dollars.'
		]);

		$v->each('name', ['required']);
		$v->each('checkout', ['required', 'integer']);
		$v->each('currency', ['required', 'in:EUR,USD,GBP']);
		$v->each('number', ['required', 'integer']);
		$v->each('table_fee', ['dollar_format']);
		$v->each('audit_adjust', ['dollar_format']);

		if ($v->fails()) {
			return redirect()->back()->withErrors($v);
		}

		$ids      = $request->input('id');
		$names    = $request->input('name');
		$checkout = $request->input('checkout');
		$currency = $request->input('currency');

		// Parse our dollar formats for table fees/audit adjustments
		$table_fees = array();
		foreach($request->input('table_fee') as $fee) {
			if ($fee != '')
				$table_fees[] = (float)preg_replace("/([^0-9\\.-])/i", "", $fee);
		}

		$audit_adjusts = array();
		foreach($request->input('audit_adjust') as $fee) {
		if ($fee != '')
			$audit_adjusts[] = (float)preg_replace("/([^0-9\\.-])/i", "", $fee);
		}


		foreach ($request->input('number') as $i => $vendor_num) {
			if ($vendor_num) {

				try {
					if ($ids[$i])
						$vendor = Vendor::findOrFail($ids[$i]);
					else
						$vendor = Vendor::whereName($names[$i])->firstOrFail();
					$vendor->name    = $names[$i];
					$vendor->payment = $currency[$i];
					$vendor->save();
				} catch (ModelNotFoundException $e) {
					$vendor          = new Vendor;
					$vendor->name    = $names[$i];
					$vendor->payment = $currency[$i];
					$vendor->save();
				}
				try {
					$vendor->bazaars()->findOrFail($id);
					$vendor->bazaars()->updateExistingPivot($id, [
						'vendor_number' => $vendor_num,
						'checkout' => $checkout[$i],
						'table_fee' => $table_fees[$i],
						'audit_adjust' => $audit_adjusts[$i],
					]);
				} catch (ModelNotFoundException $e) {
					$vendor->bazaars()->attach($id, [
						'vendor_number' => $vendor_num,
						'checkout' => $checkout[$i],
						'table_fee' => $table_fees[$i],
						'audit_adjust' => $audit_adjusts[$i],
					]);
				}
			}
		}
		return Redirect::back()->with('message', "Everything updated successfully.");
	}

	/**
	 * AJAX request to destroy a vendor
	 *
	 * @return void
	 * @author Hannah
	 */
	public function removeVendor($id, Request $request)
	{
		$vendor = Vendor::findOrFail($request->input('input'))->first();
		$vendor->bazaars()->detach($id);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$bazaar = Bazaar::findOrFail($id);
		$bazaar->destroy();

		return Redirect::back();
	}

}

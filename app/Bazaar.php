<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

use App\BazaarVendor;

class Bazaar extends Model {

	protected $fillable = [
		'name',
		'start_date',
		'end_date'
	];

	public function vendors() {
		return $this->belongsToMany('\App\Vendor')->withPivot('vendor_number')->orderBy('vendor_number');
	}
	
	public function salesSheets() {
		return $this->hasMany('\App\SalesSheet');
	}
	
	public function newPivot(Model $parent, array $attributes, $table, $exists)
	{
		return new \App\BazaarVendor($parent, $attributes, $table, $exists);
	}
	
	/**
	 * Convert the start and end dates of the bazaar to a nicely readable string
	 *
	 * @return A "pretty" string showing the start and end dates of the bazaar
	 * @author Hannah
	 */
	public function dates() {
		$format1 = "F j";
		$format2 = "j, Y";
		
		if($this->start_date->format("m") != $this->end_date->format("m")) {
			$format2 = "F j, Y";
		}
		if($this->start_date->format("Y") != $this->end_date->format("Y")) {
			$format1 = "F j, Y";
			$format2 = "F j, Y";
			
		}
		return $this->start_date->format($format1) . "-" . $this->end_date->format($format2);
	}
	
	/**
	 * Set the start date to a database-friendly value
	 *
	 * @return void
	 * @author Hannah
	 */
	public function setStartDateAttribute($date)
	{
		$this->attributes['start_date'] = Carbon::createFromFormat('m/d/Y', $date)->setTime(0,0,0);
	}
	
	/**
	 * Set the end date to a database-friendly value
	 *
	 * @return void
	 * @author Hannah
	 */
	public function setEndDateAttribute($date)
	{
		$this->attributes['end_date'] = Carbon::createFromFormat('m/d/Y', $date)->setTime(23, 59, 59);
	}
	
	/**
	 * Returns our date columns (overrides native functionality)
	 *
	 * @return array of date columns
	 * @author Hannah
	 */
	public function getDates()
	{
		return array('start_date', 'end_date', 'created_at', 'updated_at');
	}

	/**
	 * Returns whether all sales sheets for this bazaar are validated or not
	 *
	 * @return bool
	 */
	public function isValidated()
	{
		foreach($this->vendors as $vendor) {
			if(!$vendor->isValidated()) {
				return false;
			}
		}
		return true;
	}
}

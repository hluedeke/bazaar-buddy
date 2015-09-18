<?php namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class BazaarVendor extends Pivot {
	
	public function bazaar() {
		return $this->belongsTo('\App\Bazaar');
	}
	
	public function vendor() {
		return $this->belongsTo('\App\Vendor');
	}
	
	public function sales_sheets() {
		return $this->hasMany('\App\SalesSheet');
	}
}
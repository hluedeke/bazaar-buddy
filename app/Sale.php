<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model {

	protected $fillable = [
		'sales_type',
		'validated',
		'receipt_number',
		'terminal_id',
		'sequence_id',
		'amount'
	];

	public function sales_type() {
		return $this->belongsTo('\App\SalesType');
	}
	
	public function sales_sheet() {
		return $this->belongsTo('\App\SalesSheet');
	}
	
	public function setAmountAttribute($value) {
		if($value != '')
			$this->attributes['amount'] = (float)preg_replace("/([^0-9\\.])/i", "", $value);
	}
}

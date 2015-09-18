<?php namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

use App\Validated;
use App\SalesType;

class SalesSheet extends Model {

	protected $fillable = [
		'date_of_sales',
		'sheet_number'
	];

	public function bazaar() {
		return $this->belongsTo('App\Bazaar');
	}
	
	public function vendor() {
		return $this->belongsTo('App\Vendor');
	}
	
	public function sales() {
		return $this->hasMany('\App\Sale');
	}
	
	public function createdBy() {
		return $this->belongsTo('\App\User', 'created_by');
	}
	
	public function validatedBy() {
		return $this->belongsTo('\App\User', 'validated_by');
	}
	
	// Total hack, but couldn't find a better way to do it
	// Accesses the vendor number from the pivot table
	public function vendorNumber() {
		$vendor_id = $this->vendor->id;
		return $this->bazaar->vendors()->newPivotStatementForId($vendor_id)
			->first()->vendor_number;
	}
	
	/**
	 * undocumented function
	 *
	 * @return void
	 * @author Hannah
	 */
	public function getValidationStatus ()
	{
		if($this->validated_by == null)
			return Validated::PENDING;
		
		foreach($this->sales()->whereSalesType(SalesType::CARD)->get() as $sale) {
			if(!Validated::isValid($sale->validated)) {
				return $sale->validated;
			}
		}
		
		if($this->sales()->whereValidated(Validated::LAYAWAY)->count() > 0)
			return Validated::LAYAWAY;
		
		return Validated::CORRECT;
	}
	
	/**
	 * Calculates total cash sales for this sales sheet
	 *
	 * @return void
	 * @author Hannah
	 */
	public function cash() {
		$total = 0;
		
		foreach($this->sales as $sale) {
			if($sale->sales_type == SalesType::CASH) {
				$total += $sale['amount'];
			}
		}
		
		return $total;
	}
	
	/**
	 * Calculates total credit sales for this sales sheet
	 *
	 * @return void
	 * @author Hannah
	 */
	public function credit() {
		$total = 0;
		
		foreach($this->sales as $sale) {
			if($sale->sales_type == SalesType::CARD) {
				$total += $sale['amount'];
			}
		}
		
		return $total;
	}
	
	/**
	 * Calculates total credit sales for this sales sheet
	 *
	 * @return void
	 * @author Hannah
	 */
	public function layaway() {
		$total = 0;
		
		foreach($this->sales as $sale) {
			if($sale->sales_type == SalesType::LAYAWAY) {
				$total += $sale['amount'];
			}
		}
		
		return $total;
	}
	
	/**
	 * Calculates total layaway sales for this sales sheet
	 *
	 * @return total sales
	 * @author Hannah
	 */
	public function totalSales($sales = null)
	{
		$total = 0;
		if($sales == null) {
			foreach($this->sales as $sale) {
				$total += $sale['amount'];
			}
		}
		else {
			foreach($sales as $sale) {
				$total += $sale['amount'];
			}
		}
		
		return $total;
	}
	
	// Updated At accessor
	public function getUpdatedAtAttribute($value) {
		return Carbon::createFromFormat('Y-m-d H:i:s', $value)->format('m/d/Y');
	}
	
	/**
	 * Sets the date of sales attribute with a Carbon date
	 *
	 * @return void
	 * @author Hannah
	 */
	public function setDateOfSalesAttribute($date)
	{
		$this->attributes['date_of_sales'] = Carbon::createFromFormat('m/d/Y', $date);
	}
	
	/**
	 * Returns our date columns (overrides native functionality)
	 *
	 * @return array of date columns
	 * @author Hannah
	 */
	public function getDates()
	{
		return array('date_of_sales', 'created_at', 'updated_at');
	}
}

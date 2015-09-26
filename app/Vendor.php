<?php namespace App;

use Illuminate\Database\Eloquent\Model;


class Vendor extends Model
{

	protected $fillable = [
		'name',
		'payment'
	];


	public function bazaars()
	{
		return $this->belongsToMany('\App\Bazaar')->withPivot(
			'vendor_number',
			'checkout',
			'table_fee',
			'audit_adjust'
		)->withTimestamps();
	}

	public function salesSheets()
	{
		return $this->hasMany('\App\SalesSheet', 'vendor_id');
	}

	public function newPivot(Model $parent, array $attributes, $table, $exists)
	{
		return new \App\BazaarVendor($parent, $attributes, $table, $exists);
	}

	// Total hack, but couldn't find a better way to do it
	// Accesses the vendor number from the pivot table
	public function vendorNumber($bazaar)
	{
		$vendor_id = $this->id;
		return $bazaar->vendors()->newPivotStatementForId($vendor_id)
			->first()->vendor_number;
	}

	public function checkout($bazaar)
	{
		return $bazaar->vendors()->newPivotStatementForId($this->id)
			->first()->checkout;
	}

	public function tableFee($bazaar)
	{
		return $bazaar->vendors()->newPivotStatementForId($this->id)
			->first()->table_fee;
	}

	public function auditAdjust($bazaar)
	{
		return $bazaar->vendors()->newPivotStatementForId($this->id)
			->first()->audit_adjust;
	}

	/**
	 * Returns whether all of the associated sales sheets for this vendor have
	 * been completely validated or not
	 *
	 * @return bool
	 */
	public function isValidated()
	{
		foreach ($this->salesSheets as $sheet) {
			if ($sheet->getValidationStatus() != Validated::CORRECT) {
				return false;
			}
		}
		return true;
	}
}

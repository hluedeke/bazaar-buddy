<?php namespace App;

use Illuminate\Database\Eloquent\Model;

use App\SalesType;

class CurrentVendor extends Model {

	protected $table = 'current_vendors';
	
	// We do not save on this view. It will never work.
	public function save(array $options = array())
	{
		return false;
	}
	
	public function salesSheets() {
		$bazaar = AppSettings::bazaar();
		return SalesSheet::whereVendorId($this->id)->whereBazaarId($bazaar)->get();
	}
	
	/**
	 * Takes a formatted vendor string and explodes it into useful data
	 *
	 * @return vendor instance
	 * @author Hannah
	 */
	public static function fromString($string)
	{		
		$vendor = NULL;
		
		$pieces = explode('-', $string);
		if(count($pieces) >= 2) {
			$vendor = CurrentVendor::whereVendorNumber(trim($pieces[0]))->firstOrFail();
		}
		else if(is_numeric($pieces))
			$vendor = CurrentVendor::whereVendorNumber($pieces)->firstOrFail();
		else {
			$vendor = CurrentVendor::whereName($pieces)->firstOrFail();
		}
		
		return $vendor;
	}

	/**
	 * @param $string
	 * @return int|null|string
	 */
	public static function numberFromString($string)
	{
		$vendor = NULL;

		$pieces = explode('-', $string);
		if (count($pieces) >= 2)
			$vendor = trim($pieces[0]);
		else if (is_numeric($pieces[0]))
			$vendor = $pieces[0];

		return $vendor;
	}
	
	/**
	 * Calculates total cash sales for this vendor at this bazaar
	 *
	 * @return total cash sales for vendor at this bazaar
	 * @author Hannah
	 */
	public function cash() {
		$total = 0;
		
		foreach($this->salesSheets() as $sheet) {
			foreach($sheet->sales as $sale) {
				if($sale->sales_type == SalesType::CASH)
					$total += $sale->amount;
			}
		}
		
		return $total;
	}
	
	/**
	 * Calculates total credit sales for this vendor at this bazaar
	 *
	 * @return total credit sales for vendor at this bazaar
	 * @author Hannah
	 */
	public function credit() {
		$total = 0;
		
		foreach($this->salesSheets() as $sheet) {
			foreach($sheet->sales as $sale) {
				if($sale->sales_type == SalesType::CARD)
					$total += $sale->amount;
			}
		}
		
		return $total;
	}
	
	/**
	 * Calculates total layaway sales for this vendor at this bazaar
	 *
	 * @return total layaway sales for vendor at this bazaar
	 * @author Hannah
	 */
	public function layaway() {
		$total = 0;
		
		foreach($this->salesSheets() as $sheet) {
			foreach($sheet->sales as $sale) {
				if($sale->sales_type == SalesType::LAYAWAY)
					$total += $sale->amount;
			}
		}
		
		return $total;
	}
	
	/**
	 * Calculates total sales for this vendor at this bazaar
	 *
	 * @return total sales for vendor at this bazaar
	 * @author Hannah
	 */
	public function totalSales() {
		$total = 0;
		
		foreach($this->salesSheets() as $sheet) {
			foreach($sheet->sales as $sale) {
				$total += $sale->amount;
			}
		}
		
		return $total;
	}

	/**
	 * Returns whether all of the associated sales sheets for this vendor have
	 * been completely validated or not
	 *
	 * @return bool
	 */
	public function isValidated()
	{
		foreach ($this->salesSheets() as $sheet) {
			if ($sheet->getValidationStatus() != Validated::CORRECT) {
				return false;
			}
		}
		return true;
	}
}

<?php namespace App;

class Validated extends LookupModel {

	const PENDING = "Pending";
	const CORRECT = "Correct";
	const MISSING = "Missing";
	const INCORRECT = "Incorrect";
	const LAYAWAY = "Layaway";
	const CH_CORRECTED = "Chair Corrected";
	
	/**
	 * undocumented function
	 *
	 * @return bool
	 * @author Hannah
	 */
	public static function isValid($string)
	{
		if($string == Validated::CORRECT || $string == Validated::CH_CORRECTED) {
			return true;
		}
		return false;
	}
	
	public static function isPending($string) {
		if($string == Validated::PENDING || $string == Validated::LAYAWAY) {
			return true;
		}
		return false;
	}

	public static function statusSearch($string)
	{
		$data = array();
		foreach(Validated::values() as $v) {
			if(stripos($v, $string) !== false)
				$data[] = $v;
		}
		return $data;
	}
}

<?php namespace App;

class LookupModel {
	
	protected static $types;
	
	/**
	 * Returns all the constants that define our LookupModel
	 *
	 * @return array of constants (key = constant name, value = constant value)
	 * @author Hannah
	 */
	public static function types() {
		if(self::$types === null)
			self::$types = (new \ReflectionClass(get_called_class()))->getConstants();
		return self::$types;
	}
	
	/**
	 * Returns all the constant values for a dropdown field
	 *
	 * @return array of constants (key = constant value, value = constant value)
	 * @author Hannah
	 */
	public static function values() {
		$values = array();
		foreach(self::types() as $name => $value) {
			$values[$value] = $value;
		}
		return $values;
	}
}
	
?>
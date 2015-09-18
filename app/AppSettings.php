<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class AppSettings extends Model
{

    protected $fillable = ['name', 'setting'];
    protected $primaryKey = 'name';

    /**
     * returns a fee from the app settings table, otherwise returns a default amount
     *
     * @return Fee from app settings table
     * @author Hannah
     */
    public static function fee()
    {
        try {
            $fee = self::whereName('bazaar_fee')->firstOrFail();
            return $fee->setting / 100;
        } catch (MethodNotFoundException $e) {
            return .16;
        }
    }

    /**
     * returns a credit card fee from the app settings table, otherwise returns a default amount
     *
     * @return Fee from app settings table
     * @author Hannah
     */
    public static function creditCardFee()
    {
        try {
            $fee = self::whereName('credit_card_fee')->firstOrFail();
            return $fee->setting / 100;
        } catch (MethodNotFoundException $e) {
            return .0199;
        }
    }

    /**
     * returns a bazaar id for the current bazaar
     *
     * @return Fee from app settings table
     * @author Hannah
     */
    public static function bazaar()
    {
        try {
            $bazaar = self::whereName('current_bazaar')->firstOrFail();
            return $bazaar->setting;
        } catch (MethodNotFoundException $e) {
            return 1;
        }
    }

}

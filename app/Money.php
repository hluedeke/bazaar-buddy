<?php
/**
 * Created by PhpStorm.
 * User: mrsluedeke
 * Date: 10/10/16
 * Time: 11:29 AM
 */

namespace App;


class Money
{
    public static function format($money, $ignore = false) {
        if($money && is_numeric($money)) {
            return '$' . number_format($money, 2);
        }
        if(!$money && !$ignore)
            return '0';
        if($ignore)
            return $money;
        return 'NaN';
    }
}
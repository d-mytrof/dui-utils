<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use yii\base\Component;

/**
 * Class for input data validation
 */
class DuiValidation extends Component
{
    /**
     * Check $value is number
     * @param type $value
     * @return type
     */
    public static function isNumber($value)
    {
        return (filter_var($value, FILTER_VALIDATE_INT) || filter_var($value, FILTER_VALIDATE_FLOAT) ? true : false);
    }

    /**
     * Check $value is integer
     * @param type $value
     * @return type
     */
    public static function isInteger($value)
    {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    /**
     * Check $value is float
     * @param type $value
     * @return type
     */
    public static function isFloat($value)
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT);
    }

    /**
     * Check $value is email
     * @param type $value
     * @return type
     */
    public static function isEmail($value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check $value is url
     * @param type $value
     * @return type
     */
    public static function isURL($value)
    {
        return filter_var($value, FILTER_VALIDATE_URL);
    }

    /**
     * Check $value is mobile phone (Example: +38(999)999-99-99)
     * @param type $value
     * @return type
     */
    public static function isPhoneUA($value)
    {
        return preg_match("/^\+38\(\d{3}\)\d{3}-\d{2}-\d{2}$/", $value);
    }

    /**
     * Check $value is date (Example: 01-12-2015)
     * @param type $value
     * @return type
     */
    public static function isDate($value)
    {
        return preg_match(
            "/(^(((0[1-9]|[12][0-8])[-](0[1-9]|1[012]))|((29|30|31)[-]".
            "(0[13578]|1[02]))|((29|30)[-](0[4,6,9]|11)))[-](19|[2-9][0-9])\d\d$)|".
            "(^29[-]02[-](19|[2-9][0-9])(00|04|08|12|16|20|24|28|32|36|40|44|48|52|".
            "56|60|64|68|72|76|80|84|88|92|96)$)/",
            $value
        );
    }

    /**
     * Check $value is time (Example: 22:10:59)
     * @param type $value
     * @return type
     */
    public static function isTime($value)
    {
        return preg_match("/^([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])/", $value);
    }

    /**
     * Check $value is short time (Example: 22:10)
     * @param type $value
     * @return type
     */
    public static function isShortTime($value)
    {
        return preg_match("/^(([0-1][0-9]|2[0-3]):([0-5][0-9]))$/", $value);
    }

    /**
     * Check $value is strong password (Example: abcdEFGH12)
     * @param type $value
     * @return type
     */
    public static function isStrongPassword($value)
    {
        return preg_match("#.*^(?=.{10,30})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#", $value);
    }

    /**
     * Check $value is in range
     * @param type $value
     * @param type $min
     * @param type $max
     * @return type
     */
    public static function isInRange($value, $min, $max)
    {
        return ($min <= $value) && ($value <= $max);
    }

    /**
     * Check $value is valid user ID (Example UID: d00001000050)
     * @param type $value
     * @return type
     */
    public static function isUID($value)
    {
        return preg_match("/^[a-z]{1}[0-9]{11}$/", $value);
    }

    /**
     * Check $value is valid unique ID (Example UNID: d000100000200000001)
     * @param type $value
     */
    public static function isUNID($value)
    {
        return preg_match("/^[a-z]{1}[0-9]{19}$/", $value);
    }
}

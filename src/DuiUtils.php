<?php
/**
 * @copyright Copyright &copy; Dmytro Mytrofanov, 2014 - 2023
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;
use stdClass;
use Collator;

/**
 * Utils class.
 */
class DuiUtils
{
    //return user IP
    public static function getIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null;
        }
        return $ip;
    }

    /**
     * sort array by current language
     * @param array $array
     */
    public static function aSortArray(&$array)
    {
        switch (Yii::$app->config->languageShortName()) {
            case 'ua':
                $collator = new Collator('ua_UA');
                break;
            case 'ru':
                $collator = new Collator('ru_RU');
                break;
            default:
                $collator = new Collator('en_US');
                break;
        }

        $collator->asort($array);
    }

    public static function getURLByLength($length = null)
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = $length != null ? substr($protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 0, $length) :
                $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        return $url;
    }

    /**
     * generate random string
     * @param type $length
     * @return string
     */
    public static function renderRandomString($length = 16, $alternate = false, $lower = false)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz' .
                (!$lower ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : '') .
                ($alternate ? '!@#$%^&*()|{}' : '');
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * generate random number
     * @param type $length
     * @return string
     */
    public static function renderRandomNumber($length = 16)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /**
     * generate random unique ID
     * @param type $length
     * @return string
     */
    public static function uniqid($length = 16, $lower = false)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return sha1($randomString . microtime(true));
    }

    /**
     * generate random unique number ID
     * @param type $length
     * @return string
     */
    public static function uniqn($length = 20)
    {
        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return substr(str_replace([',', '.'], '', microtime(true)).$randomString, 0, $length);
    }

    //check email is correct
    public static function isEmail($email)
    {
        return preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $email);
    }

    //check integer is correct
    public static function isInteger($value)
    {
        if (!preg_match('/^\d+$/', $value)) {
            return false;
        } else {
            return true;
        }
    }

    //check NAME is correct
    public static function isName($name)
    {
        $str = "!@#$%^&*()+=-[]\\\';,./{}|\":<>?";
        for ($i = 0; $i < strlen($name); $i++) {
            if (strpos($str, $name[$i]) !== false) {
                return false;
            }
        }
        return true;
    }

    /**
     * Create activation code.
     * @param string email
     */
    public static function generateActivationCode($email)
    {
        return substr(sha1(mt_rand(10000, 99999) . time() . $email), 0, 40);
    }

    /**
     * check value is alphanumeric.
     * @return true or false
     */
    public static function alphaNum($str)
    {
        return !preg_match('/[^-_@. 0-9A-Za-z]/', $str);
    }

    /**
     * check value is alpha.
     * @return true or false
     */
    public static function alpha($str)
    {
        return !preg_match('/[^-_@. A-Za-z]/', $str);
    }

    /**
     * sanitize filename
     */
    public static function prepareFileName($fileName)
    {
        return substr(preg_replace('/[^a-zA-Z0-9-_\.]/', '', $fileName), 0, 50);
    }

    /**
     * Create hash code.
     */
    public static function generateHashCode()
    {
        return sha1(time() . mt_rand(10000, 999999) . time());
    }

    //check passrod is strong (consists of A-Z, a-z 0-9)
    public static function isStrongPassword($passwword)
    {
        if (preg_match("#.*^(?=.{6,30})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).*$#", $passwword)) {
            return true;
        } else {
            return false;
        }
    }

    //remove js from value
    public static function stripJS($value)
    {
        return preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $value);
    }

    //create correct url
    public static function makeURL($value)
    {
        return str_replace('\\', '/', $value);
    }

    //get site root dir
    public static function getRootDir()
    {
        return dirname(Yii::$app->basePath);
    }

    //get ini string
    public static function arrayToIniString($array)
    {
        $res = '';
        foreach ($array as $key => $value) {
            //echo $key.'  '.$value.'<br>';
            $res .= $key . '=' . $value . "\r\n";
        }
        return $res;
        //pr($array);
    }

    //parse array to object
    public static function parseArrayToObject($array)
    {
        $object = new stdClass();
        if (is_array($array) && count($array) > 0) {
            foreach ($array as $name => $value) {
                if (!empty($name)) {
                    $object->$name = $value;
                }
            }
        }
        return $object;
    }

    public static function getVersion()
    {
        echo 'Version: 1.0 Beta.';
    }

    public static function getTime()
    {
        echo 'T:' . sprintf('%0.5f', Yii::getLogger()->getElapsedTime()) . ' s. M: ' .
            round(memory_get_peak_usage() / (1024 * 1024), 2) . ' MB';
    }

    public static function mbUcfirst(string $string)
    {
        $strlen = mb_strlen($string);
        $firstChar = mb_substr($string, 0, 1);
        $then = mb_substr($string, 1, $strlen - 1);
        return mb_strtoupper($firstChar) . $then;
    }

    public static function firstDate($date)
    {
        if ($date == date('Y-m-d', 0)) {
            return true;
        } elseif ($date == date('d-m-Y', 0)) {
            return true;
        }
        return false;
    }

    public static function arrayInsertAfter(array $array, $key, array $new)
    {
        $keys = array_keys($array);
        $index = array_search($key, $keys);
        $pos = false === $index ? count($array) : $index + 1;
        return array_merge(array_slice($array, 0, $pos), $new, array_slice($array, $pos));
    }

    public static function arrayInsertBefore(array $array, $key, array $new)
    {
        $keys = array_keys($array);
        $index = array_search($key, $keys);
        $pos = false === $index ? count($array) : $index;
        return array_merge(array_slice($array, 0, $pos), $new, array_slice($array, $pos));
    }

    public static function getClassName($object)
    {
        return (new \ReflectionClass($object))->getShortName();
    }

    public static function clearSpecial($value)
    {
        return str_replace([' ', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '=', '+',
            '.', ',', '(', ')', '[', ']', '{', '}', '\'', '"'], '-', $value);
    }

    public static function trAlias()
    {
        return 'module.'.Yii::$app->controller->module->id;
    }

    /**
     * @return string
     */
    public static function uniqOrderNumber(): string
    {
        return str_replace([',', '.'], '', microtime(true)).self::renderRandomNumber(2);
    }

    /**
     * @param string $path
     * @return string|null
     */
    public static function isCustomView(string $path): ?string
    {
        $alias = '@app/services/' . Yii::$app->id . '/' . $path;
        $path = Yii::getAlias($alias);
        $pathinfo = pathinfo($path);
        if (!isset($pathinfo['extension'])) {
            $path = $path.'.php';
        }
        return is_readable($path) ? $alias : null;
    }
}

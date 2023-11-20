<?php
/**
 * @copyright Copyright &copy; Dmytro Mytrofanov, 2014 - 2023
 * @package dui-utils
 * @version 1.0.0
 */

namespace components;

use Yii;
use yii\base\Component;
use yii\helpers\Url;
use components\DuiConfig;

/**
 * URL class.
 */
class DuiURL extends Component
{
    //redirect to URL
    public function redirect($redirectTo = '', $replace = true, $http_response_code = 302)
    {
        header("Location: $redirectTo", $replace, $http_response_code);
        return;
    }

    //redirect to URL with language
    public function langRedirect($redirectTo = '', $replace = true, $http_response_code = 302)
    {
        self::redirect(self::prepareURL($redirectTo), $replace, $http_response_code);
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

    //create URL
    public static function prepareURL($url)
    {
        return Url::toRoute('/' . DuiConfig::languageShortName()) . ($url != '' ? '/' . $url : '');
    }

    //create base URL
    public static function getBaseURL($url = null)
    {
        return Url::base(true) . ($url ? '/' . $url : '');
    }

    //create base URL with language
    public static function getBaseFullURL($url = null)
    {
        return Url::base(true) . ($url ? '/' . DuiConfig::languageShortName() . '/' . $url : '');
    }

    //create full URL
    public static function prepareFullURL($url)
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        return $protocol . $_SERVER['HTTP_HOST'] . Url::toRoute('/' . DuiConfig::languageShortName()) . ($url != '' ? '/' . $url : '');
    }

    //create full URL without language
    public static function fullURL($url)
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        return $protocol . $_SERVER['HTTP_HOST'] . ($url != '' ? '/' . $url : '');
    }

    //create URL
    public static function prepareNoLangURL($url)
    {
        return Url::toRoute('/') . ($url != '' ? '/' . $url : '');
    }

    //replace param with value if exists if not add
    public static function replaceParamWithQueryString($param, $newValue)
    {
        $params = explode('&', urldecode(\Yii::$app->request->queryString));
        $exists = false;

        foreach ($params as $key => $value) {
            if ($key == $param) {
                unset($params[$key]);
                array_unshift($params, $param . '=' . $newValue);
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            $params[$key] = $newValue;
        }

        return implode('&', $params);
    }

    //get url with new language $lang
    public static function getURLWithLang($lang, $availableLasnguages, $url)
    {
        if ($url == '/') {
            return '';
        }
        $language = substr($url, 1, strlen($lang));
        if (in_array($language, $availableLasnguages)) {
            return $lang . substr($url, strlen($lang) + 1);
        } else {
            if ($url[0] == '/') {
                if (in_array($lang, $availableLasnguages)) {
                    $url = $lang . '/' . mb_substr($url, 1, null, 'UTF-8');
                } else {
                    $url = mb_substr($url, 1, null, 'UTF-8');
                }
            }
        }
        return $url;
    }
}

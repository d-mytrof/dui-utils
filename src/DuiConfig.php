<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;
use yii\base\Component;

class DuiConfig extends Component
{
    /**
     * check value is language
     * @param string|null $language
     * @return bool
     */
    public static function isLanguage(?string $language): bool
    {
        switch ($language) {
            case 'en':
                return true;
                break;
            case 'ua':
                return true;
                break;
            case 'ru':
                return true;
                break;
        }
        return false;
    }

    /**
     * get current language
     * @return string|null
     * @SuppressWarnings(PHPMD)
     */
    public static function detectLanguage(): ?string
    {
        $language = Yii::$app->settings->getParam('defaultLanguage');

        if (!Yii::$app instanceof Application) {
            $lang = self::isLanguage(Yii::$app->request->get('language')) ?
                    Yii::$app->request->get('language') : null;
            if (Yii::$app->request->headers->get('Lang')) {
                return Yii::$app->country->languageToLocale(Yii::$app->request->headers->get('Lang'));
            }
            if (self::isLanguage($lang)) {
                return Yii::$app->country->languageToLocale($lang);
            }
            if (isset(Yii::$app->request->cookies['language']) &&
                self::isLanguage(Yii::$app->request->cookies['language'])) {
                return Yii::$app->country->languageToLocale(Yii::$app->request->cookies['language']);
            }
            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $browserLanguage = locale_get_primary_language(locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']));
                if (isset(Yii::$app->settings->getParam('availableLanguagesAliases')[$browserLanguage])) {
                    $language = Yii::$app->settings->getParam('availableLanguagesAliases')[$browserLanguage];
                }
            }
        }

        return $language;
    }

    /**
     * get current language locale
     * @return string|null
     */
    public static function languageLocale(): ?string
    {
        return Yii::$app->country->languageToLocale(self::detectLanguage());
    }

    /**
     * @param string $value
     * @return string|null
     */
    public static function languageShortName(string $value = null): ?string
    {
        return Yii::$app->country->localeToLanguage($value ? $value : self::detectLanguage());
    }

    /**
     * @return array
     * @SuppressWarnings(PHPMD)
     */
    public function getCountriesIds(): array
    {
        $data = [];
        $list = Yii::$app->settings->getParam('availableCountries');
        if (count($list)) {
            foreach ($list as $index => $item) {
                $data[] = $index;
            }
        }
        return $data;
    }

    /**
     * @param int $countryID
     * @return array
     * @SuppressWarnings(PHPMD)
     */
    public function getCitiesIds(int $countryID): array
    {
        $data = [];
        $list = isset(Yii::$app->settings->getParam('availableCities')[$countryID]) ?
                Yii::$app->settings->getParam('availableCities')[$countryID] : [];
        if (count($list)) {
            foreach ($list as $index => $item) {
                $data[] = $item;
            }
        }
        return $data;
    }

    /**
     * @param int $countryID
     * @return array|null
     */
    public function getAvailableLanguagesList(int $countryID): ?array
    {
        $data = [];
        if (isset(Yii::$app->settings->getParam('availableCountries')[$countryID])) {
            foreach (Yii::$app->settings->getParam('availableCountries')[$countryID]['availableLanguages'] as $key => $value) {
                $data[$key] = Yii::t('basic', $value);
            }
        }
        return $data;
    }
}

<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;
use yii\base\Component;
use Locale;

class DuiCountry extends Component
{
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;
    
    public static $sort = true;
    public static $byCitiesFromConfig = false;

    /**
     * @param string $entityClassName
     * @param int $id
     * @return string
     */
    public static function getCountryNameById(string $entityClassName, int $id): string
    {
        $entity = new $entityClassName;
        $model = $entity::find()
                ->where(['=', 'id', $id])
                ->select(['id', 'country'])
                ->one();
        if ($model) {
            return Yii::t('countries', strtoupper($model->country));
        }
        return '';
    }
    
    /**
     * @SuppressWarnings(PHPMD)
     * @return array
     */
    private static function getCountriesList(): array
    {
        return [
            1 => Yii::t('countries', 'AD'),
            2 => Yii::t('countries', 'AE'),
            3 => Yii::t('countries', 'AF'),
            4 => Yii::t('countries', 'AG'),
            5 => Yii::t('countries', 'AI'),
            6 => Yii::t('countries', 'AL'),
            7 => Yii::t('countries', 'AM'),
            8 => Yii::t('countries', 'AO'),
            9 => Yii::t('countries', 'AQ'),
            10 => Yii::t('countries', 'AR'),
            11 => Yii::t('countries', 'AS'),
            12 => Yii::t('countries', 'AT'),
            13 => Yii::t('countries', 'AU'),
            14 => Yii::t('countries', 'AW'),
            15 => Yii::t('countries', 'AX'),
            16 => Yii::t('countries', 'AZ'),
            17 => Yii::t('countries', 'BA'),
            18 => Yii::t('countries', 'BB'),
            19 => Yii::t('countries', 'BD'),
            20 => Yii::t('countries', 'BE'),
            21 => Yii::t('countries', 'BF'),
            22 => Yii::t('countries', 'BG'),
            23 => Yii::t('countries', 'BH'),
            24 => Yii::t('countries', 'BI'),
            25 => Yii::t('countries', 'BJ'),
            26 => Yii::t('countries', 'BL'),
            27 => Yii::t('countries', 'BM'),
            28 => Yii::t('countries', 'BN'),
            29 => Yii::t('countries', 'BO'),
            30 => Yii::t('countries', 'BQ'),
            31 => Yii::t('countries', 'BR'),
            32 => Yii::t('countries', 'BS'),
            33 => Yii::t('countries', 'BT'),
            34 => Yii::t('countries', 'BV'),
            35 => Yii::t('countries', 'BW'),
            36 => Yii::t('countries', 'BY'),
            37 => Yii::t('countries', 'BZ'),
            38 => Yii::t('countries', 'CA'),
            39 => Yii::t('countries', 'CC'),
            40 => Yii::t('countries', 'CD'),
            41 => Yii::t('countries', 'CF'),
            42 => Yii::t('countries', 'CG'),
            43 => Yii::t('countries', 'CH'),
            44 => Yii::t('countries', 'CI'),
            45 => Yii::t('countries', 'CK'),
            46 => Yii::t('countries', 'CL'),
            47 => Yii::t('countries', 'CM'),
            48 => Yii::t('countries', 'CN'),
            49 => Yii::t('countries', 'CO'),
            50 => Yii::t('countries', 'CR'),
            51 => Yii::t('countries', 'CU'),
            52 => Yii::t('countries', 'CV'),
            53 => Yii::t('countries', 'CW'),
            54 => Yii::t('countries', 'CX'),
            55 => Yii::t('countries', 'CY'),
            56 => Yii::t('countries', 'CZ'),
            57 => Yii::t('countries', 'DE'),
            58 => Yii::t('countries', 'DJ'),
            59 => Yii::t('countries', 'DK'),
            60 => Yii::t('countries', 'DM'),
            61 => Yii::t('countries', 'DO'),
            62 => Yii::t('countries', 'DZ'),
            63 => Yii::t('countries', 'EC'),
            64 => Yii::t('countries', 'EE'),
            65 => Yii::t('countries', 'EG'),
            66 => Yii::t('countries', 'EH'),
            67 => Yii::t('countries', 'ER'),
            68 => Yii::t('countries', 'ES'),
            69 => Yii::t('countries', 'ET'),
            70 => Yii::t('countries', 'FI'),
            71 => Yii::t('countries', 'FJ'),
            72 => Yii::t('countries', 'FK'),
            73 => Yii::t('countries', 'FM'),
            74 => Yii::t('countries', 'FO'),
            75 => Yii::t('countries', 'FR'),
            76 => Yii::t('countries', 'GA'),
            77 => Yii::t('countries', 'GB'),
            78 => Yii::t('countries', 'GD'),
            79 => Yii::t('countries', 'GE'),
            80 => Yii::t('countries', 'GF'),
            81 => Yii::t('countries', 'GG'),
            82 => Yii::t('countries', 'GH'),
            83 => Yii::t('countries', 'GI'),
            84 => Yii::t('countries', 'GL'),
            85 => Yii::t('countries', 'GM'),
            86 => Yii::t('countries', 'GN'),
            87 => Yii::t('countries', 'GP'),
            88 => Yii::t('countries', 'GQ'),
            89 => Yii::t('countries', 'GR'),
            90 => Yii::t('countries', 'GS'),
            91 => Yii::t('countries', 'GT'),
            92 => Yii::t('countries', 'GU'),
            93 => Yii::t('countries', 'GW'),
            94 => Yii::t('countries', 'GY'),
            95 => Yii::t('countries', 'HK'),
            96 => Yii::t('countries', 'HM'),
            97 => Yii::t('countries', 'HN'),
            98 => Yii::t('countries', 'HR'),
            99 => Yii::t('countries', 'HT'),
            100 => Yii::t('countries', 'HU'),
            101 => Yii::t('countries', 'ID'),
            102 => Yii::t('countries', 'IE'),
            103 => Yii::t('countries', 'IL'),
            104 => Yii::t('countries', 'IM'),
            105 => Yii::t('countries', 'IN'),
            106 => Yii::t('countries', 'IO'),
            107 => Yii::t('countries', 'IQ'),
            108 => Yii::t('countries', 'IR'),
            109 => Yii::t('countries', 'IS'),
            110 => Yii::t('countries', 'IT'),
            111 => Yii::t('countries', 'JE'),
            112 => Yii::t('countries', 'JM'),
            113 => Yii::t('countries', 'JO'),
            114 => Yii::t('countries', 'JP'),
            115 => Yii::t('countries', 'KE'),
            116 => Yii::t('countries', 'KG'),
            117 => Yii::t('countries', 'KH'),
            118 => Yii::t('countries', 'KI'),
            119 => Yii::t('countries', 'KM'),
            120 => Yii::t('countries', 'KN'),
            121 => Yii::t('countries', 'KP'),
            122 => Yii::t('countries', 'KR'),
            123 => Yii::t('countries', 'KW'),
            124 => Yii::t('countries', 'KY'),
            125 => Yii::t('countries', 'KZ'),
            126 => Yii::t('countries', 'LA'),
            127 => Yii::t('countries', 'LB'),
            128 => Yii::t('countries', 'LC'),
            129 => Yii::t('countries', 'LI'),
            130 => Yii::t('countries', 'LK'),
            131 => Yii::t('countries', 'LR'),
            132 => Yii::t('countries', 'LS'),
            133 => Yii::t('countries', 'LT'),
            134 => Yii::t('countries', 'LU'),
            135 => Yii::t('countries', 'LV'),
            136 => Yii::t('countries', 'LY'),
            137 => Yii::t('countries', 'MA'),
            138 => Yii::t('countries', 'MC'),
            139 => Yii::t('countries', 'MD'),
            140 => Yii::t('countries', 'ME'),
            141 => Yii::t('countries', 'MF'),
            142 => Yii::t('countries', 'MG'),
            143 => Yii::t('countries', 'MH'),
            144 => Yii::t('countries', 'MK'),
            145 => Yii::t('countries', 'ML'),
            146 => Yii::t('countries', 'MM'),
            147 => Yii::t('countries', 'MN'),
            148 => Yii::t('countries', 'MO'),
            149 => Yii::t('countries', 'MP'),
            150 => Yii::t('countries', 'MQ'),
            151 => Yii::t('countries', 'MR'),
            152 => Yii::t('countries', 'MS'),
            153 => Yii::t('countries', 'MT'),
            154 => Yii::t('countries', 'MU'),
            155 => Yii::t('countries', 'MV'),
            156 => Yii::t('countries', 'MW'),
            157 => Yii::t('countries', 'MX'),
            158 => Yii::t('countries', 'MY'),
            159 => Yii::t('countries', 'MZ'),
            160 => Yii::t('countries', 'NA'),
            161 => Yii::t('countries', 'NC'),
            162 => Yii::t('countries', 'NE'),
            163 => Yii::t('countries', 'NF'),
            164 => Yii::t('countries', 'NG'),
            165 => Yii::t('countries', 'NI'),
            166 => Yii::t('countries', 'NL'),
            167 => Yii::t('countries', 'NO'),
            168 => Yii::t('countries', 'NP'),
            169 => Yii::t('countries', 'NR'),
            170 => Yii::t('countries', 'NU'),
            171 => Yii::t('countries', 'NZ'),
            172 => Yii::t('countries', 'OM'),
            173 => Yii::t('countries', 'PA'),
            174 => Yii::t('countries', 'PE'),
            175 => Yii::t('countries', 'PF'),
            176 => Yii::t('countries', 'PG'),
            177 => Yii::t('countries', 'PH'),
            178 => Yii::t('countries', 'PK'),
            179 => Yii::t('countries', 'PL'),
            180 => Yii::t('countries', 'PM'),
            181 => Yii::t('countries', 'PN'),
            182 => Yii::t('countries', 'PR'),
            183 => Yii::t('countries', 'PS'),
            183 => Yii::t('countries', 'PT'),
            185 => Yii::t('countries', 'PW'),
            186 => Yii::t('countries', 'PY'),
            187 => Yii::t('countries', 'QA'),
            188 => Yii::t('countries', 'RE'),
            189 => Yii::t('countries', 'RO'),
            190 => Yii::t('countries', 'RS'),
            191 => Yii::t('countries', 'RU'),
            192 => Yii::t('countries', 'RW'),
            193 => Yii::t('countries', 'SA'),
            194 => Yii::t('countries', 'SB'),
            195 => Yii::t('countries', 'SC'),
            196 => Yii::t('countries', 'SD'),
            197 => Yii::t('countries', 'SE'),
            198 => Yii::t('countries', 'SG'),
            199 => Yii::t('countries', 'SH'),
            200 => Yii::t('countries', 'SI'),
            201 => Yii::t('countries', 'SJ'),
            202 => Yii::t('countries', 'SK'),
            203 => Yii::t('countries', 'SL'),
            204 => Yii::t('countries', 'SM'),
            205 => Yii::t('countries', 'SN'),
            206 => Yii::t('countries', 'SO'),
            207 => Yii::t('countries', 'SR'),
            208 => Yii::t('countries', 'SS'),
            209 => Yii::t('countries', 'ST'),
            210 => Yii::t('countries', 'SV'),
            211 => Yii::t('countries', 'SX'),
            212 => Yii::t('countries', 'SY'),
            213 => Yii::t('countries', 'SZ'),
            214 => Yii::t('countries', 'TC'),
            215 => Yii::t('countries', 'TD'),
            216 => Yii::t('countries', 'TF'),
            217 => Yii::t('countries', 'TG'),
            218 => Yii::t('countries', 'TH'),
            219 => Yii::t('countries', 'TJ'),
            220 => Yii::t('countries', 'TK'),
            221 => Yii::t('countries', 'TL'),
            222 => Yii::t('countries', 'TM'),
            223 => Yii::t('countries', 'TN'),
            224 => Yii::t('countries', 'TO'),
            225 => Yii::t('countries', 'TR'),
            226 => Yii::t('countries', 'TT'),
            227 => Yii::t('countries', 'TV'),
            227 => Yii::t('countries', 'TW'),
            228 => Yii::t('countries', 'TZ'),
            229 => Yii::t('countries', 'UA'),
            230 => Yii::t('countries', 'UG'),
            231 => Yii::t('countries', 'UM'),
            232 => Yii::t('countries', 'US'),
            233 => Yii::t('countries', 'UY'),
            234 => Yii::t('countries', 'UZ'),
            235 => Yii::t('countries', 'VA'),
            236 => Yii::t('countries', 'VC'),
            237 => Yii::t('countries', 'VE'),
            238 => Yii::t('countries', 'VG'),
            239 => Yii::t('countries', 'VI'),
            240 => Yii::t('countries', 'VN'),
            241 => Yii::t('countries', 'VU'),
            241 => Yii::t('countries', 'WF'),
            243 => Yii::t('countries', 'WS'),
            244 => Yii::t('countries', 'YE'),
            245 => Yii::t('countries', 'YT'),
            246 => Yii::t('countries', 'ZA'),
            247 => Yii::t('countries', 'ZM'),
            248 => Yii::t('countries', 'ZW'),
        ];
    }

    /**
     * Countries list
     * @param array $available
     * @return array
     */
    public static function getList(array $available = []): array
    {
        $list = self::getCountriesList();
        if (count($available)) {
            $data = [];
            foreach ($list as $index => $item) {
                if (array_search($index, $available) !== false) {
                    $data[$index] = $item;
                }
            }
            $list = $data;
        }

        //sort array
        if (self::$sort) {
            DuiUtils::aSortArray($list);
        }

        return $list;
    }

    /**
     * SuppressWarnings(PHPMD)
     * @param string $entityClassName
     * @param string $language
     * @param int $countryID
     * @return array|null
     */
    public static function getCities(
        string $entityClassName,
        string $language = null,
        int $countryID = null
    ): ?array {
        $entity = new $entityClassName;
        $query = $entity::find()->select([
                    'id',
                    'obl',
                    'city_en', 'city_region_en',
                    'city_ua', 'city_region_ua',
                    'city_ru', 'city_region_ru'
                ])->
                where([
            'status' => self::STATUS_ACTIVE,
        ]);
        if (self::$byCitiesFromConfig) {
            $query->andWhere(['in', 'id', Yii::$app->config->getCitiesIds($countryID)]);
        }
        $cities = $query->asArray()->all();
        $result = [];
        $obl = [];
        foreach ($cities as $key => $value) {
            $language = !empty($language) ? $language : Yii::$app->config->languageShortName($language);
            $cityRegion = $value['city_region_' . $language];
            if ($language) {
                if ($value['obl'] == 0) {
                    $result[$value['id']] = $value['city_' . $language] . (!empty($cityRegion) ? ' (' . $cityRegion . ')' : '');
                } else {
                    $obl[$value['id']] = $value['city_' . $language];
                }
            }
        }

        //sort array
        DuiUtils::aSortArray($result);

        return $obl + $result;
    }

    public static function languageToLocale($value)
    {
        switch ($value) {
            case 'en':
                return 'en-US';
                break;
            case 'ua':
                return 'uk-UA';
                break;
            case 'ru':
                return 'ru-RU';
                break;
            case 'ge':
                return 'ka-GE';
                break;
        }
        return $value;
    }

    public static function localeToLanguage(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $locale = str_replace('_', '-', $value);
        $lang   = Locale::getPrimaryLanguage($locale);

        return match ($lang) {
            'uk' => 'ua',
            'ka' => 'ge',
            'en' => 'en',
            'ru' => 'ru',
            'fr' => 'fr',
            'de' => 'de',
            'es' => 'es',
            'it' => 'it',
            'pt' => 'pt',
            'zh' => 'zh',
            'ja' => 'ja',
            'ko' => 'ko',
            'ar' => 'ar',
            'tr' => 'tr',
            'nl' => 'nl',
            'sv' => 'sv',
            'da' => 'da',
            'no' => 'no',
            'fi' => 'fi',
            'pl' => 'pl',
            'cs' => 'cs',
            'hu' => 'hu',
            'el' => 'el',
            'ro' => 'ro',
            'bg' => 'bg',
            'he' => 'he',
            'hi' => 'hi',
            'bn' => 'bn',
            'id' => 'id',
            'th' => 'th',
            'vi' => 'vi',

            default => $lang,
        };
    }
}

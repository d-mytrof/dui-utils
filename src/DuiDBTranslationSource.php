<?php
/**
 * @copyright Copyright &copy; Dmytro Mytrofanov, 2014 - 2023
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use yii\i18n\PhpMessageSource;
use models\Translation;
use Yii;
use yii\helpers\ArrayHelper;

class DuiDBTranslationSource extends PhpMessageSource
{
    protected function loadMessages($category, $language)
    {
        $messages = [];
        $items = Translation::findAll(['group' => $category]);
        $languageShortName = Yii::$app->config->languageShortName($language ?? null);

        if ($items && $languageShortName) {
            $messages = ArrayHelper::map($items, 'key_name', $languageShortName);
        }

        return (array) $messages;
    }
}

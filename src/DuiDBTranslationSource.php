<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use yii\i18n\PhpMessageSource;
use Yii;
use yii\helpers\ArrayHelper;

class DuiDBTranslationSource extends PhpMessageSource
{
    public $entityClassName;
    
    protected function loadMessages($category, $language)
    {
        $messages = [];
        $entity = new $this->entityClassName;
        $items = $entity::findAll(['group' => $category]);
        $languageShortName = Yii::$app->config->languageShortName($language ?? null);

        if ($items && $languageShortName) {
            $messages = ArrayHelper::map($items, 'key_name', $languageShortName);
        }

        return (array) $messages;
    }
}

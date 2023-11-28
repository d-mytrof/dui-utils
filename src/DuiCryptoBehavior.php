<?php
/**
 * @copyright Copyright &copy; Dmytro Mytrofanov, 2014 - 2023
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use yii\base\Behavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use Yii;

class DuiCryptoBehavior extends Behavior
{
    public $attributes = [];

    /**
     * @return array
     */
    public function events(): array
    {
        return [
            ActiveRecord::EVENT_BEFORE_INSERT => function () {
                $this->encode();
            },
            ActiveRecord::EVENT_BEFORE_UPDATE => function () {
                $this->encode();
            },
            ActiveRecord::EVENT_AFTER_FIND => function () {
                $this->decode();
            },
            ActiveRecord::EVENT_AFTER_INSERT => function () {
                $this->decode();
            },
            ActiveRecord::EVENT_AFTER_UPDATE => function () {
                $this->decode();
            },
        ];
    }

    /**
     * @return void
     */
    protected function decode(): void
    {
        foreach ($this->attributes as $attribute) {
            $value = $this->owner->getAttribute($attribute);

            if ($value === null) {
                $this->owner->setAttribute($attribute, $value);
                continue;
            }

            if ($value instanceof Expression) {
                $this->owner->refresh();
            }

            if (is_string($value)) {
                $this->owner->setAttribute(
                    $attribute,
                    Yii::$app->appSecurity->getDecryptedString($this->owner->db, $value)
                );
                continue;
            }
        }
    }

    /**
     * @return void
     */
    protected function encode(): void
    {
        foreach ($this->attributes as $attribute) {
            if (!array_key_exists($attribute, $this->owner->dirtyAttributes)) {
                continue;
            }
            $fieldValue = $this->owner->getAttribute($attribute);
            $this->owner->setAttribute(
                $attribute,
                Yii::$app->appSecurity->getEncrypted((string) $fieldValue) ?: null
            );
        }
    }
}

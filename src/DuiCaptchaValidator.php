<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;
use yii\validators\Validator;
use dmytrof\utils\DuiCaptchaWidget;

class DuiCaptchaValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        $captcha = new DuiCaptchaWidget();

        if (!$captcha->valid($model->captcha_hash, $model->captcha_value)) {
            $this->addError($model, $attribute, Yii::t('basic', 'CAPTCHA_IS_INCORRECT'));
            return false;
        }
        if (!$captcha->isAlive($model->captcha_hash)) {
            $this->addError($model, $attribute, Yii::t('basic', 'CAPTCHA_LIFETIME_IS_OVER'));
            return false;
        }
    }
}

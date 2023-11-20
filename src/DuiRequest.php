<?php
/**
 * @copyright Copyright &copy; Dmytro Mytrofanov, 2014 - 2023
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;

class DuiRequest extends \yii\web\Request
{
    /**
     * @param string $value
     * @param mixed $defaultValue
     * @return string|null
     */
    public function getParameter(string $value, mixed $defaultValue = null): ?string
    {
        $parameter = Yii::$app->request->get($value);
        if (!$parameter) {
            $parameter = Yii::$app->request->post($value);
        }
        if (!$parameter) {
            $parameter = Yii::$app->request->getBodyParam($value);
        }
        if (!$parameter) {
            $parameter = Yii::$app->request->getHeaders()->get($value);
        }
        if (!$parameter) {
            $parameter = $defaultValue;
        }

        return $parameter;
    }
}

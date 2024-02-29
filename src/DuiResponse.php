<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;

class DuiResponse extends \yii\web\Response
{
    public $responseMessage;

    public static function getBeforeSend($event)
    {
        Yii::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Origin', '*');
        Yii::$app->getResponse()->getHeaders()->set('Origin', '*');
        Yii::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Credentials', 'true');
        Yii::$app->getResponse()->getHeaders()->set(
            'Access-Control-Allow-Headers',
            'Origin, Content-Type, Accept, Lang, Authorization, X-Requested-With'
        );
        Yii::$app->getResponse()->getHeaders()->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, HEAD, OPTIONS');
        Yii::$app->getResponse()->getHeaders()->set('Access-Control-Max-Age', '86400');

        $response = $event->sender;
        $result = [];

        if ($response->data !== null) {
            if (isset($response->data['data'])) {
                $result = is_array($response->data['data']) ?
                        array_merge($result, $response->data['data']) : $response->data['data'];
            }

            if (isset($response->data['error'])) {
                $result = array_merge($result, ['error' => $response->data['error']]);
            }

            if (isset($response->data['message'])) {
                $result = array_merge($result, ['message' => $response->data['message']]);
            }

            if (isset($response->data['notes'])) {
                $result = array_merge($result, ['notes' => $response->data['notes']]);
            }

            if (isset($response->data['total'])) {
                $result = array_merge($result, ['total' => $response->data['total']]);
            }

            if (isset($response->data['pageCount'])) {
                $result = array_merge($result, ['pageCount' => $response->data['pageCount']]);
            }

            if (isset($response->data['currentPage'])) {
                $result = array_merge($result, ['currentPage' => $response->data['currentPage']]);
            }

            if (isset($response->data['perPage'])) {
                $result = array_merge($result, ['perPage' => $response->data['perPage']]);
            }

            if (isset($response->data['html'])) {
                $result = array_merge($result, ['html' => $response->data['html']]);
            }

            if ($result) {
                $response->data = $result;
            }
        }
    }
}

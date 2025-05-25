<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use Yii;
use yii\rest\Controller;
use yii\base\Action;

abstract class DuiRestAction extends Action
{
    public $request;
    public $response;

    /**
     * @param int $id
     * @param Controller $controller
     * @param array $config
     */
    public function __construct($id, Controller $controller, array $config = [])
    {
        parent::__construct($id, $controller, $config);
        $this->request = Yii::$app->getRequest();
        $this->response = [];
    }

    /**
     * @param mixed $dataProvider
     * @param int $page
     * @return array
     */
    public function getDataProviderResponse(mixed $dataProvider, int $page = null): array
    {
        $total = $dataProvider->totalCount;
        $perPage = $dataProvider->pagination ? $dataProvider->pagination->pageSize : null;
        $this->response['data']['items'] = $dataProvider;
        if ($perPage) {
            $this->response['data']['pageCount'] = ceil($total / $perPage);
            $this->response['data']['perPage'] = $perPage;
            $this->response['data']['page'] = $dataProvider->pagination->page + 1;
            if ($page) {
                $this->response['data']['page'] = $page;
            }
        }
        $this->response['data']['total'] = $total;

        return $this->response;
    }
}

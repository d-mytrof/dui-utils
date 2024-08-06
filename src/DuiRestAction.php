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
     * @param int $currentPage
     * @return array
     */
    public function getDataProviderResponse(mixed $dataProvider, int $currentPage = null): array
    {
        $total = $dataProvider->totalCount;
        $perPage = $dataProvider->pagination->pageSize;
        $this->response['data']['result'] = $dataProvider;
        $this->response['data']['pageCount'] = ceil($total / $perPage);
        $this->response['data']['perPage'] = $perPage;
        $this->response['data']['currentPage'] = $dataProvider->pagination->page + 1;
        if ($currentPage) {
            $this->response['data']['currentPage'] = $currentPage;
        }
        $this->response['data']['total'] = $total;

        return $this->response;
    }
}

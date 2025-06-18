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
     * @return array
     */
    public function getDataProviderResponse(mixed $dataProvider): array
    {
        $total = $dataProvider->totalCount;
        $perPage = $dataProvider->pagination ? $dataProvider->pagination->pageSize : null;

        $this->response['data']['items'] = $dataProvider;

        if ($perPage) {
            $totalPages = ceil($total / $perPage);
            $currentPage = $dataProvider->pagination->page + 1;

            if ($currentPage > $totalPages && $totalPages > 0) {
                $this->response['data']['items'] = [];
                $this->response['data']['pageCount'] = $totalPages;
                $this->response['data']['perPage'] = $perPage;
                $this->response['data']['page'] = $currentPage;
                $this->response['data']['total'] = $total;

                return $this->response;
            }

            $this->response['data']['pageCount'] = $totalPages;
            $this->response['data']['perPage'] = $perPage;
            $this->response['data']['page'] = $currentPage;
        }

        $this->response['data']['total'] = $total;

        return $this->response;
    }
}

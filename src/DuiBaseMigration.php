<?php
/**
 * @copyright Copyright © 2024 Dmytro Mytrofanov
 * @package dui-utils
 * @version 1.0.0
 */

namespace dmytrof\utils;

use yii\db\Migration;

class DuiBaseMigration extends Migration
{
    protected $tableOptions = null;

    public function init()
    {
        parent::init();
        if ($this->db->driverName === 'mysql') {
            $this->tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
    }

    public function createTable($table, $columns, $options = null)
    {
        if ($options === null) {
            $options = $this->tableOptions;
        }
        parent::createTable($table, $columns, $options);
    }
}

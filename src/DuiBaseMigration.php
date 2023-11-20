<?php
/**
 * @copyright Copyright &copy; Dmytro Mytrofanov, 2014 - 2023
 * @package dui-utils
 * @version 1.0.0
 */

namespace components;

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

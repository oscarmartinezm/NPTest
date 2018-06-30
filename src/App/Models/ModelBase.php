<?php

namespace App\Models;

use Libs\DataBase;

/**
 * Description of ModelBase
 *
 * @author oscar
 */
class ModelBase {

    private static $db = null;

    protected function __construct() {
        
    }

    protected function _save($table, $data, $id = null) {
        if (is_null($id)) {
            $this->insert($table, $data);
        } else {
            $this->update($table, $data, ['id' => $id]);
        }
    }

    protected function insert($table, $data) {
        $data['insert_on'] = date('Y-m-d H:i:s');
        $data['update_on'] = date('Y-m-d H:i:s');
        $sql = self::createInsert($table, array_keys($data));
        self::db()->execute($sql, array_values($data));
    }

    protected function update($table, $data, $filters) {
        $data['update_on'] = date('Y-m-d H:i:s');
        $values = array_values($data);
        $valuesFilters = array_values($filters);
        $sql = self::createUpdate($table, array_keys($data), array_keys($filters));
        self::db()->execute($sql, array_merge($values, $valuesFilters));
    }

    protected static function _find($table, $filters, $orderBy = array('id')) {
        $valuesFilters = array_values($filters);
        $selectSQL = self::createSelect($table, array_keys($filters), $orderBy);
        return self::db()->query($selectSQL, $valuesFilters);
    }

    private static function createSelect($table, $filtersColumns, $orderBy) {
        if (empty($filtersColumns)) {
            $orderByStr = join(', ', $orderBy);
            $sql = "SELECT * FROM $table ORDER BY $orderByStr;";
        } else {
            $filtersStr = join(' = ? AND ', $filtersColumns) . ' = ?';
            $orderByStr = join(', ', $orderBy);
            $sql = "SELECT * FROM $table WHERE $filtersStr ORDER BY $orderByStr;";
        }
        return $sql;
    }

    private static function createInsert($table, $columns) {
        $columnsStr = join(', ', $columns);
        $valuesStr = str_repeat('?, ', count($columns) - 1) . '?';
        $sql = "INSERT INTO $table ({$columnsStr}) VALUES ({$valuesStr});";
        return $sql;
    }

    private static function createUpdate($table, $columns, $filtersColumns) {
        if (empty($filtersColumns)) {
            throw new \Exception('Is not possible execute update on database without any filter');
        }
        $columnsStr = join(' = ?, ', $columns) . " = ?";
        $filtersStr = join(' = ? AND ', $filtersColumns) . ' = ?';
        $sql = "UPDATE $table SET $columnsStr WHERE $filtersStr;";
        return $sql;
    }

    protected static function getRegexFilter($regex) {
        return array('options' => array('regexp' => "/{$regex}/"));
    }

    public function toArray() {
        return (array) $this;
    }

    protected static function db() {
        if (is_null(self::$db)) {
            self::$db = new DataBase(DB_USER, DB_PASS, DB_SCHEMA, DB_HOST);
        }
        return self::$db;
    }

}

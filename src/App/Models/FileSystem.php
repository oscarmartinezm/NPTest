<?php

namespace App\Models;

use App\Helpers\FileSystemTree;

/**
 * Description of FileSystem
 *
 * @author oscar
 */
class FileSystem extends ModelBase {

    public $id;
    public $name;
    public $type;
    public $parent;
    public $level;
    public $level_id;
    public $level_name;
    public $complete_path;
    private static $TABLE = 'filesystem';

    const TYPE_FILE = 'File';
    const TYPE_DIRECTORY = 'Directory';

    public function __construct() {
        $this->id = null;
        $this->name = null;
        $this->type = null;
        $this->parent = null;
        $this->level = null;
        $this->level_id = null;
        $this->level_name = null;
        $this->complete_path = null;
        parent::__construct();
    }

    public function save() {
        $validation = $this->validate();
        if ($validation !== true) {
            throw new \Exception('Some field values are not valid: ' . join(', ', $validation));
        }
        if($this->parent > 0){
            $this->complete_path = '/' . join('/', $this->getParentPath()) . '/' . $this->name;
        } else {
            $this->complete_path = '/' . $this->name;
        }
        $this->_save(self::$TABLE, [
            'name' => $this->name,
            'type' => $this->type,
            'parent' => ($this->parent == 0) ? null : $this->parent,
            'level' => $this->level,
            'complete_path' => $this->complete_path
                ], $this->id);
    }

    public function getParentPath() {
        $parentLevel = $this->level - 1;
        $selectColumns = 't1.name AS lev1, ';
        $selectJoins = '';
        for ($i = 2; $i <= $parentLevel; $i++) {
            $prevIndex = $i - 1;
            $selectColumns .= "t{$i}.name AS lev{$i}, ";
            $selectJoins .= "LEFT JOIN filesystem AS t{$i} ON t{$i}.parent = t{$prevIndex}.id ";
        }
        $selectColumns = trim($selectColumns, ', ');
        $sql = "SELECT {$selectColumns} FROM filesystem AS t1 {$selectJoins} WHERE t{$parentLevel}.id = {$this->parent} LIMIT 0,1;";
        return self::db()->queryFirst($sql);
    }

    public function validate() {
        $notValidFields = array();
        if (!filter_var($this->name, FILTER_VALIDATE_REGEXP, self::getRegexFilter('^[a-zA-Z0-9\.-_ ]{1,255}$'))) {
            $notValidFields[] = "name => {$this->name}";
        }
        if (!filter_var($this->type, FILTER_VALIDATE_REGEXP, self::getRegexFilter('^(File|Directory)$'))) {
            $notValidFields[] = "type => {$this->type}";
        }
        if (!filter_var($this->parent, FILTER_VALIDATE_INT) === 0 && !filter_var($this->parent, FILTER_VALIDATE_INT)) {
            $notValidFields[] = "parent => {$this->parent}";
        }
        if (!filter_var($this->level, FILTER_VALIDATE_INT) === 0 && !filter_var($this->level, FILTER_VALIDATE_INT)) {
            $notValidFields[] = "level => {$this->level}";
        }
        return (empty($notValidFields) ? true : $notValidFields);
    }

    public static function delete($id) {
        return parent::_delete(self::$TABLE, ['id' => $id]);
    }

    public static function find($id) {
        $dbData = parent::_find(self::$TABLE, ['id' => $id]);
        if (count($dbData) < 1) {
            return null;
        }
        $filesystem = self::createFromDB($dbData[0]);
        return $filesystem;
    }

    public static function findByName($name) {
        return parent::find(self::$TABLE, ['name' => $name]);
    }

    public static function findByType($type) {
        $dbResult = parent::find(self::$TABLE, ['type' => $type], ['parent', 'level', 'name']);
        $result = array();
        foreach ($dbResult as $item) {
            $result[] = self::createFromDB($item);
        }
        return $result;
    }

    public static function search() {
        
    }

    public static function getTree($includeFiles = true, $asArray = false) {
        $typeDirectory = self::TYPE_DIRECTORY;
        $where = !($includeFiles) ? "WHERE type = '{$typeDirectory}' " : '';
        $sql = "SELECT id, name, type, IFNULL(parent, 0) as parent, level, complete_path FROM filesystem {$where};";
        $data = self::query($sql);
        return FileSystemTree::get()->createTree($data, $asArray);
    }

    public static function getFlat($includeFiles = true) {
        $tree = self::getTree($includeFiles, true);
        return self::treeToFlat($tree);
    }

    public static function saveFromFile($filePath, $parentId, $initialLevel) {
        self::db()->execute('TRUNCATE ' . self::$TABLE);
        $tree = FileSystemTree::get()->fromFile($filePath);
        self::saveFromFileTree($tree, $parentId, $initialLevel);
    }

    protected static function createFromDB($dbData) {
        $filesystem = new FileSystem();
        $filesystem->id = $dbData['id'];
        $filesystem->name = $dbData['name'];
        $filesystem->type = $dbData['type'];
        $filesystem->parent = $dbData['parent'] ? $dbData['parent'] : 0;
        $filesystem->level = (int) $dbData['level'];
        $filesystem->complete_path = $dbData['complete_path'];
        $filesystem->level_id = $filesystem->parent . '-' . ($filesystem->level - 1);
        $filesystem->level_name = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', ($filesystem->level) - 1) . $filesystem->name;
        return $filesystem;
    }

    private static function saveFromFileTree($tree, $parentId, $initialLevel) {
        foreach ($tree as $treeItem) {
            $model = new FileSystem();
            $model->name = $treeItem['name'];
            $model->level = ($treeItem['level'] + $initialLevel);
            $model->parent = $parentId;
            $model->type = (isset($treeItem['children'])) ? self::TYPE_DIRECTORY : self::TYPE_FILE;
            $model->save();
            if ($model->type == self::TYPE_DIRECTORY) {
                self::saveFromFileTree($treeItem['children'], self::db()->getLastInsertId(), $initialLevel);
            }
        }
    }

    private static function treeToFlat($tree, &$count = 0) {
        $result = array();
        foreach ($tree as $row) {
            $result[$count] = self::createFromDB($row);
            if (isset($row['children'])) {
                $result = array_merge($result, self::treeToFlat($row['children'], $count));
            }
            ++$count;
        }
        return $result;
    }

    public static function get() {
        return new FileSystem();
    }

}

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
        parent::__construct();
    }

    private static function createFromDB($dbData) {
        $filesystem = new FileSystem();
        $filesystem->id = $dbData['id'];
        $filesystem->name = $dbData['name'];
        $filesystem->type = $dbData['type'];
        $filesystem->parent = $dbData['parent'] || 0;
        $filesystem->level = (int) $dbData['level'];
        $filesystem->level_id = $filesystem->parent . '-' . ($filesystem->level - 1);
        $filesystem->level_name = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', ($filesystem->level) - 1) . $filesystem->name;
        return $filesystem;
    }

    public function save() {
        $validation = $this->validate();
        if ($validation !== true) {
            throw new \Exception('Some field values are not valid: ' . join(', ', $validation));
        }
        $this->_save(self::$TABLE, [
            'name' => $this->name,
            'type' => $this->type,
            'parent' => ($this->parent == 0) ? null : $this->parent,
            'level' => $this->level
                ], $this->id);
    }

    public static function delete($id) {
        return parent::_delete(self::$TABLE, ['id' => $id]);
    }

    public static function find($id) {
        $dbData = parent::_find(self::$TABLE, ['id' => $id]);
        if (count($dbData) < 1) {
            return null;
        }
        return self::createFromDB($dbData[0]);
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

    public static function getTree($includeFiles = true, $asArray = false) {
        $typeDirectory = self::TYPE_DIRECTORY;
        $where = !($includeFiles) ? "WHERE type = '{$typeDirectory}' " : '';
        $sql = "SELECT id, name, type, IFNULL(parent, 0) as parent, level FROM filesystem {$where};";
        $data = self::query($sql);
        return FileSystemTree::get()->getTree($data, $asArray);
    }

    public static function getFlat($includeFiles = true) {
        $tree = self::getTree($includeFiles, true);
        return self::treeToFlat($tree);
    }

    public static function saveFromFile($filePath) {
        $tree = FileSystemTree::get()->fromFile($filePath);
        print_r($tree); die();
    }
    
    private static function _loopTree($tree) {
        $fsf = new FileSystemFile($filePath);
        $tree = $fsf->getTree();
        print_r($tree); die();
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

    public static function get() {
        return new FileSystem();
    }

}

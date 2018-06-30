<?php

namespace App\Models;

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
        $filesystem->parent = $dbData['parent'];
        $filesystem->level = (int) $dbData['level'];
        $filesystem->level_id = $filesystem->parent . '-' . ($filesystem->level - 1);
        $filesystem->level_name = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $filesystem->level) . $filesystem->name;
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
            'parent' => $this->parent,
            'level' => $this->level
                ], $this->id);
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
        $filters = !($includeFiles) ? ['type' => self::TYPE_DIRECTORY] : [];
        $data = parent::_find(self::$TABLE, $filters, ['parent', 'level', 'name']);
        if (empty($data)) {
            return array();
        }
        $parents = array();
        foreach ($data as $item) {
            $parents[$item['parent']][] = $item;
        }
        if ($asArray) {
            $tree = self::createBranchArr($parents, $parents[0]);
        } else {
            $tree = self::createBranchObj($parents, $parents[0]);
        }
        return $tree;
    }

    public static function getFlat($includeFiles = true) {
        $tree = self::getTree($includeFiles, true);
        return self::treeToFlat($tree);
    }

    private static function createBranchArr(&$parents, $children) {
        $tree = array();
        foreach ($children as $child) {
            if (isset($parents[$child['id']])) {
                $child['children'] = self::createBranchArr($parents, $parents[$child['id']]);
            }
            $tree[] = $child;
        }
        return $tree;
    }

    private static function createBranchObj(&$parents, $children) {
        $tree = array();
        foreach ($children as $child) {
            $childObj = self::createFromDB($child);
            if (isset($parents[$child['id']])) {
                $childObj->children = self::createBranchObj($parents, $parents[$child['id']]);
            }
            $tree[] = $childObj;
        }
        return $tree;
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

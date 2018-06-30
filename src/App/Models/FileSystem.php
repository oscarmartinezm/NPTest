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
        parent::__construct();
    }

    private static function createFromDB($dbData) {
        $filesystem = new FileSystem();
        $filesystem->id = $dbData['id'];
        $filesystem->name = $dbData['name'];
        $filesystem->type = $dbData['type'];
        $filesystem->parent = $dbData['parent'];
        $filesystem->level = (int) $dbData['level'];
        $filesystem->level_name = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $dbData['level']) . $dbData['name'];
        return $filesystem;
    }

    public function save($id = null) {
        $validation = $this->validate();
        if ($validation !== true) {
            throw new \Exception('Some field values are not valid: ' . join(', ', $validation));
        }
        $this->_save(self::$TABLE, [
            'name' => $this->name,
            'type' => $this->type,
            'parent' => $this->parent,
            'level' => $this->level
                ], $id);
    }

    public function findByName($name) {
        return parent::find(self::$TABLE, ['name' => $name]);
    }

    public function findByType($type) {
        $dbResult = parent::find(self::$TABLE, ['type' => $type], ['parent', 'level', 'name']);
        $result = array();
        foreach ($dbResult as $item) {
            $result[] = self::createFromDB($item);
        }
        return $result;
    }

    public function getTree($includeFiles = true, $asArray = false) {
        $filters = !($includeFiles) ? ['type' => self::TYPE_DIRECTORY] : [];
        $data = parent::find(self::$TABLE, $filters, ['parent', 'level', 'name']);
        $parents = array();
        foreach ($data as $item) {
            $parents[$item['parent']][] = $item;
        }
        if ($asArray) {
            $tree = $this->createBranchArr($parents, $parents[0]);
        } else {
            $tree = $this->createBranchObj($parents, $parents[0]);
        }
        return $tree;
    }

    public function getFlatStructure($includeFiles = true) {
        $tree = $this->getTree($includeFiles, true);
        return $this->treeToFlat($tree);
    }

    private function createTree($flat, $root = 0) {
        $parents = array();
        foreach ($flat as $a) {
            $parents[$a['parent']][] = $a;
        }
        return $this->createBranch($parents, $parents[$root]);
    }

    private function createBranchArr(&$parents, $children) {
        $tree = array();
        foreach ($children as $child) {
            if (isset($parents[$child['id']])) {
                $child['children'] = $this->createBranchArr($parents, $parents[$child['id']]);
            }
            $tree[] = $child;
        }
        return $tree;
    }

    private function createBranchObj(&$parents, $children) {
        $tree = array();
        foreach ($children as $child) {
            $childObj = self::createFromDB($child);
            if (isset($parents[$child['id']])) {
                $childObj->children = $this->createBranchObj($parents, $parents[$child['id']]);
            }
            $tree[] = $childObj;
        }
        return $tree;
    }

    private function treeToFlat($tree, &$count = 0) {
        $result = array();
        foreach ($tree as $row) {
            $result[$count] = self::createFromDB($row);
            if (isset($row['children'])) {
                $result = array_merge($result, $this->treeToFlat($row['children'], $count));
            }
            ++$count;
        }
        return $result;
    }

    public function validate() {
        $notValidFields = array();
        if (!filter_var($this->name, FILTER_VALIDATE_REGEXP, $this->getRegexFilter('^[a-zA-Z0-9\.-_ ]{1,255}$'))) {
            $notValidFields[] = "name => {$this->name}";
        }
        if (!filter_var($this->type, FILTER_VALIDATE_REGEXP, $this->getRegexFilter('^(File|Directory)$'))) {
            $notValidFields[] = "type => {$this->type}";
        }
        if (!filter_var($this->parent, FILTER_VALIDATE_INT) === 0 && !filter_var($this->parent, FILTER_VALIDATE_INT)) {
            $notValidFields[] = "parent => {$this->parent}";
        }
        if (!filter_var($this->level, FILTER_VALIDATE_INT) === 0 && !filter_var($this->level, FILTER_VALIDATE_INT)){
            $notValidFields[] = "level => {$this->level}";
        }
        return (empty($notValidFields) ? true : $notValidFields);
    }

    public static function get() {
        return new FileSystem();
    }

}

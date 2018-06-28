<?php

namespace App\Models;

/**
 * Description of FileSystem
 *
 * @author oscar
 */
class FileSystem extends ModelBase {

    public $name;
    public $type;
    public $parent;
    public $completePath;

    const TABLE = 'filesystem';

    public function __construct() {
        parent::__construct();
    }

    public function save($id = null) {
        $isValid = $this->validate();
        if(!$isValid){
            throw new \Exception('Data not valid');
        }
        $this->_save(self::TABLE, [
            'name' => $this->name,
            'type' => $this->type,
            'parent' => $this->parent,
            'complete_path' => $this->completePath
                ], $id);
    }

    public function findByName($name) {
        return parent::find(self::TABLE, ['name' => $name]);
    }

    public function validate() {
        return (
                filter_var($this->name, FILTER_VALIDATE_REGEXP, $this->getRegexFilter('^[a-zA-Z0-9\.-_ ]{1,255}$')) &&
                filter_var($this->type, FILTER_VALIDATE_REGEXP, $this->getRegexFilter('^(File|Directory)$')) &&
                (filter_var($this->parent, FILTER_VALIDATE_INT) === 0 || filter_var($this->parent, FILTER_VALIDATE_INT))
        );
    }

}

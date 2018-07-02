<?php

namespace App\Models;

/**
 * Description of Contact
 *
 * @author oscar
 */
class Contact extends ModelBase {

    public $name;
    public $email;
    public $phone_number;
    private $errors;

    public function __construct() {
        $this->name = null;
        $this->email = null;
        $this->phone_number = null;
        $this->errors = array();
        parent::__construct();
    }

    public static function saveBatch($contacsArr) {
        $handle = fopen(CONTACTS_FILE_PATH, 'w');
        foreach ($contacsArr as $item) {
            $contactArr = [$item->name, $item->email, $item->phone_number];
            fputcsv($handle, $contactArr, ',', '"');
        }
        fclose($handle);
        return CONTACTS_FILE_PATH;
    }

    public function validate() {
        $notValidFields = array();
        if (!filter_var($this->name, FILTER_VALIDATE_REGEXP, self::getRegexFilter('^[A-Za-z\ ]+$'))) {
            $notValidFields[] = 'name';
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $notValidFields[] = 'email';
        }
        if (!filter_var($this->phone_number, FILTER_VALIDATE_REGEXP, self::getRegexFilter('^[0-9]+$'))) {
            $notValidFields[] = 'phone_number';
        }
        $this->errors = $notValidFields;
        return (empty($notValidFields) ? true : $notValidFields);
    }
    
    public function getErrors(){
        return $this->errors;
    }

}

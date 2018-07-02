<?php

namespace App\Controllers;

use App\Models\Contact;

/**
 * Description of MultiformController
 *
 * @author oscar
 */
class ContactController extends ControllerBase {

    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Display a listing of the resource.
     *
     */
    public function index() {
        $this->loadView('multicontact_form/index', ['_error_' => self::$error]);
    }
    
    /**
     * Display a listing of the resource.
     *
     */
    public function save() {
        print_r($_POST); die();
    }
    
    public static function get() {
        return new ContactController();
    }
    
}

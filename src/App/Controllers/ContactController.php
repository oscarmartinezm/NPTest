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
        //$filesystem = FileSystem::getFlat(true);
        $this->loadView('multicontact_form/index', ['_error_' => self::$error]);
    }
    
    public static function get() {
        return new ContactController();
    }
    
}

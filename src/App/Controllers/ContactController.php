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
        $this->loadView('multicontact_form/index');
    }

    /**
     * Saves the resource.
     *
     */
    public function save() {
        try {
            $names = filter_input(INPUT_POST, 'name', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $emails = filter_input(INPUT_POST, 'email', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            $phoneNumbers = filter_input(INPUT_POST, 'phone_number', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
            if (count($names) != count($emails) || count($emails) != count($phoneNumbers)) {
                $this->redirect('/multicontact-form/', 'Inputs quantity doesn\'t match');
                exit();
            }
            $contacts = array();
            $failedContacts = array();
            foreach ($names as $key => $value) {
                $contact = new Contact();
                $contact->name = $names[$key];
                $contact->email = $emails[$key];
                $contact->phone_number = $phoneNumbers[$key];
                if ($contact->validate() === true) {
                    $contacts[] = $contact;
                } else {
                    $count = count($failedContacts) + 1;
                    $failedContacts[] = ["$count. $contact->name" => $contact->getErrors()];
                }
            }
            $fileLocation = Contact::saveBatch($contacts);
            if (empty($failedContacts)) {
                $this->redirect('/multicontact-form/', null, "Contacts saved successfully in {$fileLocation}");
            } else {
                $failedContactsStr = $this->formatFailedContactsStr($failedContacts);
                $this->redirect('/multicontact-form/', "We found some errors in some contacts: $failedContactsStr");
            }
        } catch (\Exception $exc) {
            $this->redirect('/filesystem/add/', 'Error saving the items');
        }
    }

    private function formatFailedContactsStr($failedContacts){
        $str = str_replace(['"', '[', ']', '{', '}'], '', json_encode($failedContacts));
        return str_replace(',', ', ', str_replace(':', ': ', $str));
    }
    
    public static function get() {
        return new ContactController();
    }

}

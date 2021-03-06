<?php

require_once 'bootstrap.php';

use App\Controllers\FileSystemController;
use App\Controllers\ContactController;

$route = filter_input(INPUT_GET, 'route');

switch (true) {
    case $route == '/multicontact-form/':
        ContactController::get()->index();
        break;
    case $route == '/multicontact-form/save/':
        ContactController::get()->save();
        break;
    case $route == '/filesystem/':
        FileSystemController::get()->index();
        break;
    case $route == '/filesystem/add/':
        FileSystemController::get()->create();
        break;
    case $route == '/filesystem/update/batch/':
        FileSystemController::get()->batch();
        break;
    case preg_match('/^\/filesystem\/update\/[0-9]+\/$/', $route):
        $id = explode('/', $route)[3];
        FileSystemController::get()->edit($id);
        break;
    case preg_match('/^\/filesystem\/[0-9]+\/$/', $route):
        $method = filter_input(INPUT_POST, '_method');
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        switch ($method) {
            case 'POST':
                FileSystemController::get()->store();
                break;
            case 'PATCH':
                FileSystemController::get()->update($id);
                break;
            case 'DELETE':
                FileSystemController::get()->destroy($id);
                break;
            default:
                break;
        }
        break;
    default:
        FileSystemController::get()->redirect('/filesystem/');
        break;
}



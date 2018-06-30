<?php

require_once 'bootstrap.php';

use App\Controllers\FileSystemController;

$route = filter_input(INPUT_GET, 'route');

switch (true) {
    case $route == '/filesystem/':
        FileSystemController::get()->index();
        break;
    case $route == '/filesystem/add/':
        FileSystemController::get()->create();
        break;
    case preg_match('/^\/filesystem\/update\/[0-9]+\/$/', $route):
        $id = explode('/', $route)[3];
        FileSystemController::get()->edit($id);
        break;
    case $route == '/filesystem/save/':
        $method = filter_input(INPUT_POST, '_method');
        if ($method === 'POST') {
            FileSystemController::get()->store();
        } elseif ($method === 'PATCH') {
            $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
            FileSystemController::get()->update($id);
        }
        break;
    default:
        FileSystemController::get()->index();
        break;
}



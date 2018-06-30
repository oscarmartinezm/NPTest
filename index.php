<?php

require_once 'bootstrap.php';

use App\Controllers\FileSystemController;

$route = filter_input(INPUT_GET, 'route');

switch ($route) {
    case '/filesystem/':
        FileSystemController::get()->index();
        break;
    case '/filesystem/add/':
        FileSystemController::get()->create();
        break;
    case '/filesystem/save/':
        $method = filter_input(INPUT_POST, '_method');
        if ($method === 'POST') {
            FileSystemController::get()->store();
        } elseif ($method === 'PATCH') {
            FileSystemController::get()->update();
        }
        break;
    default:
        FileSystemController::get()->index();
        break;
}



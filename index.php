<?php

require_once 'bootstrap.php';

use App\Controllers\FileSystemController;

$route = filter_input(INPUT_GET, 'route');

switch ($route) {
    case '/filesystem/':
        $controller = new FileSystemController();
        $controller->index();
        break;
    case '/filesystem/add/':
        $controller = new FileSystemController();
        $controller->create();
        break;
    case '/filesystem/save/':
        $controller = new FileSystemController();
        $method = filter_input(INPUT_POST, '_method');
        if ($method === 'POST') {
            $controller->store();
        } elseif ($method === 'PATCH') {
            $controller->update();
        }
        $controller->create();
        break;
    default:
        $controller = new FileSystemController();
        $controller->index();
        break;
}



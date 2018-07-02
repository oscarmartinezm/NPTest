<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('upload_tmp_dir', __DIR__ . '/_tmp');
date_default_timezone_set('America/Costa_Rica');

define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_SCHEMA', 'netpay');
define('DB_HOST', '127.0.0.1');
define('CONTACTS_FILE_PATH', __DIR__ . '/_tmp/contacts.csv');
define('TWIG_CACHE_PATH', false);
define('TWIG_VIEWS_PATH', __DIR__ . '/src/App/views/');



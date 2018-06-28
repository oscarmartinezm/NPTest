<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_SCHEMA', 'paynet');
define('DB_HOST', '127.0.0.1');
define('TWIG_CACHE_PATH', false);
define('TWIG_VIEWS_PATH', __DIR__ . '/src/App/views/');

date_default_timezone_set('America/Costa_Rica');
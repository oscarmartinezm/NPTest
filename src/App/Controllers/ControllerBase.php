<?php

namespace App\Controllers;

use Twig_Loader_Filesystem;
use Twig_Environment;

session_start();

/**
 * Description of ControllerBase
 *
 * @author oscar
 */
class ControllerBase {

    protected $twig;
    protected static $error = null;
    protected static $success = null;

    public function __construct() {
        $this->checkMessages();
        $loader = new Twig_Loader_Filesystem(TWIG_VIEWS_PATH);
        $this->twig = new Twig_Environment($loader, array(
            'cache' => TWIG_CACHE_PATH,
        ));
    }

    protected function loadView($template, $variables) {
        echo $this->twig->render($template . '.twig', $variables);
    }

    public function redirect($redirectTo, $errorMessage = null, $successMessage = null) {
        if (!is_null($errorMessage)) {
            $_SESSION['__error__'] = $errorMessage;
        }
        if (!is_null($successMessage)) {
            $_SESSION['__success__'] = $successMessage;
        }
        header("Location:index.php?route={$redirectTo}");
        exit();
    }

    protected function hasError() {
        return !is_null(self::$error);
    }
    
    private function checkMessages(){
        if (isset($_SESSION['__error__'])) {
            self::$error = $_SESSION['__error__'];
            unset($_SESSION['__error__']);
        }
        if (isset($_SESSION['__success__'])) {
            self::$success = $_SESSION['__success__'];
            unset($_SESSION['__success__']);
        }
    }

}

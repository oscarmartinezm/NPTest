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
    protected $error = null;

    public function __construct() {
        if (isset($_SESSION['__error__'])) {
            $this->error = $_SESSION['__error__'];
            unset($_SESSION['__error__']);
        }
        $loader = new Twig_Loader_Filesystem(TWIG_VIEWS_PATH);
        $this->twig = new Twig_Environment($loader, array(
            'cache' => TWIG_CACHE_PATH,
        ));
    }

    protected function loadView($template, $variables) {
        echo $this->twig->render($template . '.twig', $variables);
    }

    protected function redirect($redirectTo, $errorMessage = null) {
        if (!is_null($errorMessage)) {
            $_SESSION['__error__'] = $errorMessage;
        }
        header("Location:index.php?route={$redirectTo}");
        exit();
    }

    protected function hasError() {
        return !is_null($this->error);
    }

}

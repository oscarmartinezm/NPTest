<?php

namespace App\Controllers;

use Twig_Loader_Filesystem;
use Twig_Environment;

/**
 * Description of ControllerBase
 *
 * @author oscar
 */
class ControllerBase {

    protected $twig;

    public function __construct() {
        $loader = new Twig_Loader_Filesystem(TWIG_VIEWS_PATH);
        $this->twig = new Twig_Environment($loader, array(
            'cache' => TWIG_CACHE_PATH,
        ));
    }

    protected function loadView($template, $variables) {
        echo $this->twig->render($template . '.twig', $variables);
    }

}

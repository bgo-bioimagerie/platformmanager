<?php

require_once 'Framework/Controller.php';

class PhpinfoController extends Controller {

    public function indexAction() {
        print phpinfo();
    }

}

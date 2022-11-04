<?php

require_once 'Framework/Controller.php';

/**
 * Controler managing the user connection
 *
 * @author Sylvain Prigent
 */
class CoreconnectscriptController extends Controller
{
    /**
     * (non-PHPdoc)
     * @see Controller::index()
     *
     */
    public function indexAction()
    {
        $modelUser = new CoreUser();
        $modelUser->updateLastConnection($_SESSION['id_user']);
    }
}

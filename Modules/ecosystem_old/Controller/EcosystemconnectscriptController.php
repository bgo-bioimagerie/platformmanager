<?php

require_once 'Framework/Controller.php';
require_once 'Modules/ecosystem/Model/EcResponsible.php';
require_once 'Modules/ecosystem/Model/EcUser.php';

/**
 * Controler managing the user connection 
 * 
 * @author Sylvain Prigent
 */
class EcosystemconnectscriptController extends Controller {

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     */
    public function indexAction() {
        
        $modelEcResp = new EcResponsible();
        if( !$modelEcResp->hasResponsible($_SESSION['id_user']) ){
            $modelEcResp->setResponsible($_SESSION['id_user'], 1);
        }
        
        // set ec_user info if not exists
        $modelEcUser = new EcUser();
        if ( !$modelEcUser->exists($_SESSION['id_user']) ){
            $modelEcUser->initialize($_SESSION['id_user']);
        }
    }

}

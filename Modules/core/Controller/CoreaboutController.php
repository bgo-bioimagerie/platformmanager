<?php

require_once 'Modules/core/Controller/CorecookiesecureController.php';

require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreInstall.php';
/**
 * 
 * @author osallou
 * Controller for the about page
 */
class CoreaboutController extends CorecookiesecureController {

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction() {
        $tag = trim(exec('git describe --tags'));

        $cdb = new CoreDB();
        $crel = $cdb->getRelease();
        $need = [];
        if(isset($_SESSION['user_status']) && $_SESSION['user_status'] == CoreStatus::$ADMIN) {
            $need = $cdb->needUpgrade();
        }

        return $this->render(array(
            'metadesc' => 'pfm about page',
            'data' => [
                'tag' => $tag,
                'edb' => $cdb->getVersion(),
                'cdb' => $crel,
                'need' => $need
            ]
        ));
        
    }

    public function plansAction() {
        $plans = Configuration::get('plans', []);
        $publicPlans = [];
        foreach($plans as $p) {
            if(isset($p['custom'])) {
                continue;
            }
            $publicPlans[] = $p;
        }
        $this->render(['plans' => $publicPlans]); 
    }

    public function privacyAction() {
        $this->render([
            'url' => Configuration::get('public_url'),
            'we' => Configuration::get('operator', 'We'),
            'contact' => Configuration::get('admin_email', '---')
        ]);
    }

}

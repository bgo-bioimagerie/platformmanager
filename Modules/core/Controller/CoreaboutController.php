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

            return $this->render(array(
                'metadesc' => 'pfm about page',
                'data' => [
                    'tag' => $tag,
                    'edb' => $cdb->getVersion(),
                    'cdb' => $crel
                ]
            ));
        
    }

    public function plansAction() {
        $plans = Configuration::get('plans', []);
        $this->render(['plans' => $plans]); 
    }

    public function privacyAction() {
        $this->render([
            'url' => Configuration::get('public_url'),
            'we' => Configuration::get('operator', 'We'),
            'contact' => Configuration::get('admin_email', '---')
        ]);
    }

}

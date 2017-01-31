<?php

require_once 'Framework/Model.php';
require_once 'Modules/ecosystem/Model/EcBelonging.php';
require_once 'Modules/ecosystem/Model/EcUser.php';
require_once 'Modules/ecosystem/Model/EcUnit.php';
require_once 'Modules/ecosystem/Model/EcResponsible.php';
require_once 'Modules/ecosystem/Model/EcProject.php';

/**
 * Class defining methods to install and initialize the core database
 *
 * @author Sylvain Prigent
 */
class EcosystemInstall extends Model {

    /**
     * Create the core database
     *
     * @return boolean True if the base is created successfully
     */
    public function createDatabase() {
        
        $model = new EcBelonging();
        $model->createTable();
        
        $userModel = new EcUser();
        $userModel->createTable();
        $userModel->importCoreUsers();

        $unitModel = new EcUnit();
        $unitModel->createTable();

        $respModel = new EcResponsible();
        $respModel->createTable();

        $projectModel = new EcProject();
        $projectModel->createTable();

        if (!file_exists('data/core/')) {
            mkdir('data/core/', 0777, true);
        }
    }
}

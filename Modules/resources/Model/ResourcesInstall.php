<?php

require_once 'Framework/Model.php';
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/resources/Model/ResourceInfo.php';

/**
 * Class defining methods to install and initialize the core database
 *
 * @author Sylvain Prigent
 */
class ResourcesInstall extends Model {

    /**
     * Create the core database
     *
     * @return boolean True if the base is created successfully
     */
    public function createDatabase() {        

        $model1 = new ReArea();
        $model1->createTable();
        
        $model2 = new ReCategory();
        $model2->createTable();
        
        $model3 = new ResourceInfo();
        $model3->createTable();
        
        if (!file_exists('data/resources/')) {
            mkdir('data/resources/', 0777, true);
        }
    }
}

<?php

require_once 'Framework/Model.php';
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ReState.php';
require_once 'Modules/resources/Model/ReEventType.php';
require_once 'Modules/resources/Model/ReEvent.php';
require_once 'Modules/resources/Model/ReEventData.php';
require_once 'Modules/resources/Model/ReResps.php';
require_once 'Modules/resources/Model/ReRespsStatus.php';
require_once 'Modules/resources/Model/ReVisa.php';
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
        
        $model4 = new ReState();
        $model4->createTable();
        
        $model5 = new ReEventType();
        $model5->createTable();
        
        $model6 = new ReEvent();
        $model6->createTable();
        
        $model7 = new ReEventData();
        $model7->createTable();
        
        $model8 = new ReResps();
        $model8->createTable();
        
        $model9 = new ReRespsStatus();
        $model9->createTable();
        
        $model10 = new ReVisa();
        $model10->createTable();
        
        
        if (!file_exists('data/resources/')) {
            mkdir('data/resources/', 0777, true);
        }
    }
}

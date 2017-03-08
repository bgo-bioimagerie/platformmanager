<?php

require_once 'Framework/Model.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/services/Model/SePurchase.php';
require_once 'Modules/services/Model/SePurchaseItem.php';
require_once 'Modules/services/Model/SeOrder.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Model/SePrice.php';
require_once 'Modules/services/Model/SeOrigin.php';

/**
 * Class defining methods to install and initialize the core database
 *
 * @author Sylvain Prigent
 */
class ServicesInstall extends Model {

    /**
     * Create the core database
     *
     * @return boolean True if the base is created successfully
     */
    public function createDatabase() {

        $modelService = new SeService();
        $modelService->createTable();

        $modelServiceType = new SeServiceType();
        $modelServiceType->createTable();
        $modelServiceType->createDefault();

        $modelSePurchase = new SePurchase();
        $modelSePurchase->createTable();

        $modelSePurchaseItem = new SePurchaseItem();
        $modelSePurchaseItem->createTable();

        $modelSeOrder = new SeOrder();
        $modelSeOrder->createTable();

        $modelSeProject = new SeProject();
        $modelSeProject->createTable();

        $modelSePrice = new SePrice();
        $modelSePrice->createTable();
        
        $modelOrigin = new SeOrigin();
        $modelOrigin->createTable();

        if (!file_exists('data/services/')) {
            mkdir('data/services/', 0777, true);
        }
    }

}

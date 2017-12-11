<?php

require_once 'Framework/Model.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClCompany.php';
require_once 'Modules/clients/Model/ClPricing.php';
require_once 'Modules/clients/Model/ClUserClientAccount.php';
require_once 'Modules/clients/Model/ClClientUser.php';
require_once 'Modules/clients/Model/ClAddress.php';

/**
 * Class defining methods to install and initialize the Clients database
 *
 * @author Sylvain Prigent
 */
class ClientsInstall extends Model {

    /**
     * Create the Clients database
     */
    public function createDatabase() {

        // initialise the Provider table
        $model1 = new ClClient();
        $model1->createTable();

        $model2 = new ClCompany();
        $model2->createTable();

        $model3 = new ClPricing();
        $model3->createTable();

        $model4 = new ClUserClientAccount();
        $model4->createTable();

        $model5 = new ClClientUser();
        $model5->createTable();
        
        $model6 = new ClAddress();
        $model6->createTable();
        
    }

}

<?php

require_once 'Framework/Model.php';
require_once 'Modules/estore/Model/EsProductDefault.php';
require_once 'Modules/estore/Model/EsProductCategoryDefault.php';
require_once 'Modules/estore/Model/EsSale.php';
require_once 'Modules/estore/Model/EsSaleEnteredItem.php';
require_once 'Modules/estore/Model/EsSaleHistory.php';
require_once 'Modules/estore/Model/EsSaleItem.php';
require_once 'Modules/estore/Model/EsSaleStatus.php';
require_once 'Modules/estore/Model/EsDeliveryMethod.php';
require_once 'Modules/estore/Model/EsContactType.php';
require_once 'Modules/estore/Model/EsSaleEnteredItem.php';
require_once 'Modules/estore/Model/EsProductUnitQ.php';
require_once 'Modules/estore/Model/EsPrice.php';


/**
 * Class defining methods to install and initialize the Estore database
 *
 * @author Sylvain Prigent
 */
class EstoreInstall extends Model {

    /**
     * Create the Estore database
     */
    public function createDatabase() {

        // initialise the Provider table
        $model1 = new EsProductDefault();
        $model1->createTable();

        $model2 = new EsProductCategoryDefault();
        $model2->createTable();

        $model3 = new EsSale();
        $model3->createTable();

        $model4 = new EsSaleHistory();
        $model4->createTable();

        $model5 = new EsSaleItem();
        $model5->createTable();

        $model6 = new EsDeliveryMethod();
        $model6->createTable();
        
        $model7 = new EsContactType();
        $model7->createTable();
        
        $model8 = new EsSaleEnteredItem();
        $model8->createTable();
        
        $model9 = new EsProductUnitQ();
        $model9->createTable();
        
        $model10 = new EsPrice();
        $model10->createTable();
        
        
    }

}

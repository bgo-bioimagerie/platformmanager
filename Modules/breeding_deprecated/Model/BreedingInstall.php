<?php

require_once 'Framework/Model.php';

require_once 'Modules/breeding/Model/BrBatch.php';
require_once 'Modules/breeding/Model/BrLosse.php';
require_once 'Modules/breeding/Model/BrLosseType.php';
require_once 'Modules/breeding/Model/BrTreatment.php';
require_once 'Modules/breeding/Model/BrChipping.php';
require_once 'Modules/breeding/Model/BrSale.php';
require_once 'Modules/breeding/Model/BrSaleItem.php';

require_once 'Modules/breeding/Model/BrCategory.php';
require_once 'Modules/breeding/Model/BrProduct.php';


/**
 * Class defining methods to install and initialize the Breeding database
 *
 * @author Sylvain Prigent
 */
class BreedingInstall extends Model {

    /**
     * Create the Breeding database
     */
    public function createDatabase() {

        $model7 = new BrBatch();
        $model7->createTable();

        $model8 = new BrLosse();
        $model8->createTable();

        $model9 = new BrLosseType();
        $model9->createTable();

        $model10 = new BrTreatment();
        $model10->createTable();

        $model11 = new BrChipping();
        $model11->createTable();

        $model13 = new BrSale();
        $model13->createTable();

        $model14 = new BrSaleItem();
        $model14->createTable();
        
        $model15 = new BrCategory();
        $model15->createTable();
        
        $model16 = new BrProduct();
        $model16->createTable();
        
        
    }

}

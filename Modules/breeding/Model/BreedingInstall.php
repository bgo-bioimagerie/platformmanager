<?php

require_once 'Framework/Model.php';

require_once 'Modules/breeding/Model/BrBatch.php';
require_once 'Modules/breeding/Model/BrLosse.php';
require_once 'Modules/breeding/Model/BrLosseType.php';
require_once 'Modules/breeding/Model/BrTreatment.php';
require_once 'Modules/breeding/Model/BrChipping.php';
require_once 'Modules/breeding/Model/BrSale.php';
require_once 'Modules/breeding/Model/BrSaleItem.php';
require_once 'Modules/breeding/Model/BrProductStage.php';
require_once 'Modules/breeding/Model/BrPrice.php';
require_once 'Modules/breeding/Model/BrProductUnitQ.php';


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

        $model16 = new BrProductStage();
        $model16->createTable();

        $model17 = new BrPrice();
        $model17->createTable();
        
        $model18 = new BrProductUnitQ();
        $model18->createTable();
        
    }

}

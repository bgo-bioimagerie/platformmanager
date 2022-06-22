<?php

require_once 'Framework/Model.php';
require_once 'Modules/booking/Model/BkColorCode.php';

require_once 'Modules/booking/Model/BkCalendarEntry.php';
require_once 'Modules/booking/Model/BkScheduling.php';
require_once 'Modules/booking/Model/BkBookingTableCSS.php';
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/booking/Model/BkPackage.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';
require_once 'Modules/booking/Model/BkCalSupInfo.php';
require_once 'Modules/booking/Model/BkAuthorization.php';
require_once 'Modules/booking/Model/BkNightWE.php';
require_once 'Modules/booking/Model/BkPrice.php';
require_once 'Modules/booking/Model/BkOwnerPrice.php';
require_once 'Modules/booking/Model/BkCalendarPeriod.php';
require_once 'Modules/booking/Model/BkRestrictions.php';

/**
 * Class defining methods to install and initialize the core database
 *
 * @author Sylvain Prigent
 */
class BookingInstall extends Model {

    /**
     * Create the core database
     *
     * @return boolean True if the base is created successfully
     */
    public function createDatabase() {        

        $model1 = new BkCalendarEntry();
        $model1->createTable();

        $model2 = new BkColorCode();
        $model2->createTable();
        
        $model3 = new BkScheduling();
        $model3->createTable();
        
        $model4 = new BkBookingTableCSS();
        $model4->createTable();
        
        $model5 = new BkBookingSettings();
        $model5->createTable();
        
        $model6 = new BkCalSupInfo();
        $model6->createTable();
        
        $model7 = new BkAccess();
        $model7->createTable();
        
        $model8 = new BkPackage();
        $model8->createTable();
        
        $model9 = new BkCalQuantities();
        $model9->createTable();
        
        $model10 = new BkAuthorization();
        $model10->createTable();
        
        $model11 = new BkNightWE();
        $model11->createTable();
        
        $model12 = new BkPrice();
        $model12->createTable();
        
        $model13 = new BkOwnerPrice();
        $model13->createTable();
        
        $model14 = new BkCalendarPeriod();
        $model14->createTable();
        
        $model15 = new BkRestrictions();
        $model15->createTable();
       
        if (!file_exists('data/booking/')) {
            mkdir('data/booking/', 0755, true);
        }
    }
}

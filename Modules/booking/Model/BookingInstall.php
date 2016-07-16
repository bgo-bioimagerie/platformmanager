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
require_once 'Modules/booking/Model/BkCalSupinfo.php';

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
        $model5->defaultEntries();
        
        $model6 = new BkCalSupinfo();
        $model6->createTable();
        
        $model7 = new BkAccess();
        $model7->createTable();
        
        $model8 = new BkPackage();
        $model8->createTable();
        
        $model9 = new BkCalQuantities();
        $model9->createTable();
        
        
        if (!file_exists('data/booking/')) {
            mkdir('data/booking/', 0777, true);
        }
    }
}

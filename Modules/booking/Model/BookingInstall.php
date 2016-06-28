<?php

require_once 'Framework/Model.php';
require_once 'Modules/booking/Model/BkColorCode.php';

require_once 'Modules/booking/Model/BkCalendarEntry.php';

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
        
        if (!file_exists('data/booking/')) {
            mkdir('data/booking/', 0777, true);
        }
    }
}

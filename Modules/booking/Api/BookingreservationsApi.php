<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';

require_once 'Modules/booking/Model/BkPrice.php';
require_once 'Modules/booking/Model/BkNightWE.php';
require_once 'Modules/booking/Model/BkPackage.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingreservationsApi extends Controller {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
    }

    public function getreservationsAction($id_resource, $userlogin) {

        $modelUser = new CoreUser();
        $id_user = $modelUser->getIdByLogin($userlogin);
        if ( $id_user > 0 ){
            $modelBooking = new BkCalendarEntry();
            $data = $modelBooking->getEntriesForUserResource($id_user, $id_resource);
            echo json_encode($data);
        }
        else{
            echo json_encode(array("error" => "user does not exists"));
        }
        
    }

}

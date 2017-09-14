<?php

require_once 'Framework/Controller.php';

// ecosystem
require_once 'Modules/ecosystem/Model/EcUser.php';
require_once 'Modules/ecosystem/Model/EcUnit.php';
require_once 'Modules/ecosystem/Model/EcBelonging.php';
require_once 'Modules/ecosystem/Model/EcResponsible.php';

// resources
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/resources/Model/ReVisa.php';

// booking
require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/booking/Model/BkAuthorization.php';
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/Model/BkBookingTableCSS.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';
require_once 'Modules/booking/Model/BkCalSupInfo.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';
require_once 'Modules/booking/Model/BkColorCode.php';
require_once 'Modules/booking/Model/BkNightWe.php';
require_once 'Modules/booking/Model/BkPrice.php';
require_once 'Modules/booking/Model/BkScheduling.php';
require_once 'Modules/booking/Model/BkPackage.php';

// services
require_once 'Modules/services/Model/SeOrder.php';
require_once 'Modules/services/Model/SePrice.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Model/SeService.php';

require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';

require_once 'Modules/core/Model/CoreSpace.php';


class mergeaccountController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        
        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new Exception("Error 503: Permission denied");
        }
    }
    
    public function indexAction() {
        
        // secure
        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new Exception("Error 503: Permission denied");
        }
        
        $modelUser = new EcUser();
        $users = $modelUser->getAcivesForSelect("name");
        
        $form = new Form("mergeaccountform", $this->request);
        $formAdd = new FormAdd("mergeaccountformadd", $this->request);
        $formAdd->addSelect("user", CoreTranslator::User(), $choices, $choicesid);
    }
}

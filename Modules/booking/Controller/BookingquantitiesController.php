<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/core/Model/CoreVirtual.php';
require_once 'Modules/booking/Controller/BookingsupsabstractController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingquantitiesController extends BookingsupsabstractController {

    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        $this->modelSups = new BkCalQuantities();
        $this->supsType = "quantity";
        $this->supsTypePlural = "quantities";
        $this->invoicable = true;
        $this->mandatoryFields = true;
        $this->hasDuration = false;
        $this->formUrl = "bookingquantities";
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $form = $this->getSupForm($id_space, BookingTranslator::Quantities($lang));
        
        if ($form->check()) {
            $bkSupIds = $this->supsFormCheck($id_space);
            return $this->redirect($this->formUrl."/".$id_space, data:$bkSupIds);
        }
        // view
        $formHtml = $form->getHtml($lang);
        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            'formHtml' => $formHtml
        ));
    }
}

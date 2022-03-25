<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkCalSupInfo.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/core/Model/CoreVirtual.php';
require_once 'Modules/booking/Controller/BookingsupsabstractController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingsupsinfoController extends BookingsupsabstractController {

    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        $this->modelSups = new BkCalSupinfo();
        $this->supsType = "supinfo";
        $this->supsTypePlural = "supplementaries";
        $this->invoicable = false;
        $this->mandatoryFields = true;
        $this->formUrl = "bookingsupsinfo";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $form = $this->getSupForm($id_space, BookingTranslator::supplementaries($lang));

        if ($form->check()) {
            $this->supsFormCheck($id_space);
            $this->redirect($this->formUrl."/".$id_space);
            return;
        }
        // view
        $formHtml = $form->getHtml($lang);
        $this->render(array(
            'id_space' => $id_space,
            "lang" => $lang,
            'formHtml' => $formHtml
        ));
    }

}

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
class BookingsupsinfoController extends BookingsupsabstractController
{
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->modelSups = new BkCalSupinfo();
        $this->supsType = "supinfo";
        $this->supsTypePlural = "supplementaries";
        $this->invoicable = false;
        $this->mandatoryFields = true;
        $this->hasDuration = false;
        $this->formUrl = "bookingsupsinfo";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $form = $this->getSupForm($idSpace, BookingTranslator::supplementaries($lang));

        if ($form->check()) {
            $this->supsFormCheck($idSpace);
            $this->redirect($this->formUrl."/".$idSpace);
            return;
        }
        // view
        $formHtml = $form->getHtml($lang);
        $this->render(array(
            'id_space' => $idSpace,
            "lang" => $lang,
            'formHtml' => $formHtml
        ));
    }
}

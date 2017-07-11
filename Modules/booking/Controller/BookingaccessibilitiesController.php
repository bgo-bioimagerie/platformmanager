<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/resources/Model/ResourceInfo.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingaccessibilitiesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("bookingsettings");
        $_SESSION["openedNav"] = "bookingsettings";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        $form = new Form($this->request, "bookingaccessibilities");
        $form->setTitle(BookingTranslator::Accessibilities($lang));

        $model = new BkAccess();

        $choicesid = array(1, 2, 3, 4);
        $choices = array();
        $choices[] = BookingTranslator::User($lang);
        $choices[] = BookingTranslator::Authorized_users($lang);
        $choices[] = BookingTranslator::Manager($lang);
        $choices[] = BookingTranslator::Admin($lang);

        $modelResources = new ResourceInfo();
        $resources = $modelResources->getForSpace($id_space);
        foreach ($resources as $resource) {
            $accessId = $model->getAccessId($resource["id"]);
            $form->addSelect("r_" . $resource["id"], $resource["name"], $choices, $choicesid, $accessId);
        }

        $form->setValidationButton(CoreTranslator::Save($lang), "bookingaccessibilities/".$id_space);
        $form->setButtonsWidth(2, 9);

        if ($form->check()) {
            foreach ($resources as $resource) {
                $id_access = $this->request->getParameter("r_" . $resource["id"]);
                $model->set($resource["id"], $id_access);
            }
            $this->redirect("bookingaccessibilities/".$id_space);
        }

        // view
        $formHtml = $form->getHtml($lang);
        $this->render(array("id_space" => $id_space, "formHtml" => $formHtml, "lang" => $lang));
    }

}

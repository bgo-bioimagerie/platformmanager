<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/booking/Model/BkRestrictions.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/booking/Controller/BookingsettingsController.php';


/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BookingrestrictionsController extends BookingsettingsController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $model = new BkRestrictions();
        $modelResource = new ResourceInfo();
        $model->init($idSpace);
        $data = $model->getForSpace($idSpace);
        for ($i = 0 ; $i < count($data); $i++) {
            $data[$i]["resource"] = $modelResource->getName($idSpace, $data[$i]["id_resource"]);
        }

        //print_r($data);

        $table = new TableView();
        $table->setTitle(BookingTranslator::BookingRestriction($lang));
        $headers = array(
            "resource" => BookingTranslator::Resource(),
            "maxbookingperday" => BookingTranslator::Maxbookingperday($lang),
            "bookingdelayusercanedit" => BookingTranslator::BookingDelayUserCanEdit($lang)
        );

        $table->addLineEditButton("bookingrestrictionedit/".$idSpace);
        $tableHtml = $table->view($data, $headers);


        // view
        $this->render(array("id_space" => $idSpace, "tableHtml" => $tableHtml, "lang" => $lang));
    }

    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $model = new BkRestrictions();
        $data = $model->get($idSpace, $id);

        $modelResource = new ResourceInfo();
        $resource = $modelResource->get($idSpace, $data['id_resource']);
        $resourceName = $resource['name'];

        $form = new Form($this->request, "restrictioneditform");
        $form->setTitle(BookingTranslator::RestrictionsFor($lang) . ": " . $resourceName);

        $form->addText("maxbookingperday", BookingTranslator::Maxbookingperday($lang), false, $data["maxbookingperday"]);
        $form->addText("bookingdelayusercanedit", BookingTranslator::BookingDelayUserCanEdit($lang), false, $data["bookingdelayusercanedit"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingrestrictionedit/".$idSpace."/".$id);

        if ($form->check()) {
            $maxbookingperday = $form->getParameter("maxbookingperday");
            $bookingdelayusercanedit = $form->getParameter("bookingdelayusercanedit");
            $model->set($idSpace, $id, $maxbookingperday, $bookingdelayusercanedit);

            $this->redirect("bookingrestrictions/".$idSpace);
            return;
        }

        $this->render(array(
                'id_space' => $idSpace,
                'lang' => $lang,
                'htmlForm' => $form->getHtml($lang)
            ));
    }
}

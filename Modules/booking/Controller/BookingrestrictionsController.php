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
    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $model = new BkRestrictions();
        $modelResource = new ResourceInfo();
        $model->init($id_space);
        $data = $model->getForSpace($id_space);
        for ($i = 0 ; $i < count($data); $i++) {
            $data[$i]["resource"] = $modelResource->getName($id_space, $data[$i]["id_resource"]);
        }

        //print_r($data);

        $table = new TableView();
        $table->setTitle(BookingTranslator::BookingRestriction($lang));
        $headers = array(
            "resource" => BookingTranslator::Resource(),
            "maxbookingperday" => BookingTranslator::Maxbookingperday($lang),
            "bookingdelayusercanedit" => BookingTranslator::BookingDelayUserCanEdit($lang)
        );

        $table->addLineEditButton("bookingrestrictionedit/".$id_space);
        $tableHtml = $table->view($data, $headers);


        // view
        $this->render(array("id_space" => $id_space, "tableHtml" => $tableHtml, "lang" => $lang));
    }

    public function editAction($id_space, $id)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $model = new BkRestrictions();
        $data = $model->get($id_space, $id);

        $modelResource = new ResourceInfo();
        $resource = $modelResource->get($id_space, $data['id_resource']);
        $resourceName = $resource['name'];

        $form = new Form($this->request, "restrictioneditform");
        $form->setTitle(BookingTranslator::RestrictionsFor($lang) . ": " . $resourceName);

        $form->addText("maxbookingperday", BookingTranslator::Maxbookingperday($lang), false, $data["maxbookingperday"]);
        $form->addText("bookingdelayusercanedit", BookingTranslator::BookingDelayUserCanEdit($lang), false, $data["bookingdelayusercanedit"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingrestrictionedit/".$id_space."/".$id);

        if ($form->check()) {
            $maxbookingperday = $form->getParameter("maxbookingperday");
            $bookingdelayusercanedit = $form->getParameter("bookingdelayusercanedit");
            $model->set($id_space, $id, $maxbookingperday, $bookingdelayusercanedit);

            $this->redirect("bookingrestrictions/".$id_space);
            return;
        }

        $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'htmlForm' => $form->getHtml($lang)
            ));
    }
}

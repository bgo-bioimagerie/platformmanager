<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkBookingTableCSS.php';
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/booking/Controller/BookingsettingsController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BookingdisplayController extends BookingsettingsController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $idSpace, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        $modelArea = new ReArea();
        $areas = $modelArea->getForSpace($idSpace);

        $table = new TableView();
        $table->setTitle(BookingTranslator::Display($lang), 3);
        $table->addLineEditButton("bookingdisplayedit/".$idSpace);

        $headers = array("name" => CoreTranslator::Name($lang));

        $tableHtml = $table->view($areas, $headers);

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "tableHtml" => $tableHtml));
    }

    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelCSS = new BkBookingTableCSS();
        $data = $modelCSS->getAreaCss($idSpace, $id);

        $modelArea = new ReArea();
        $area = $modelArea->get($idSpace, $id);
        $name = $area['name'];

        $form = new Form($this->request, "bookingschedulingedit");
        $form->setTitle(BookingTranslator::Display($lang) . ": " . $name, 3);
        $form->addColor("header_background", BookingTranslator::Header_Color($lang), false, $data["header_background"]);
        $form->addColor("header_color", BookingTranslator::Header_Text($lang), false, $data["header_color"]);
        $form->addNumber("header_font_size", BookingTranslator::Header_font_size($lang), false, $data["header_font_size"]);
        $form->addNumber("resa_font_size", BookingTranslator::Resa_font_size($lang), false, $data["resa_font_size"]);
        $form->addNumber("header_height", BookingTranslator::Header_height($lang), false, $data["header_height"]);
        $form->addNumber("line_height", BookingTranslator::Line_height($lang), false, $data["line_height"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "bookingdisplayedit/".$idSpace. "/" . $id);
        $form->setColumnsWidth(3, 9);
        if ($form->check()) {
            $modelCSS->setAreaCss($idSpace, $id, $this->request->getParameter("header_background"), $this->request->getParameter("header_color"), $this->request->getParameter("header_font_size"), $this->request->getParameter("resa_font_size"), $this->request->getParameter("header_height"), $this->request->getParameter("line_height"));
            $this->redirect("bookingdisplayedit/".$idSpace. "/" . $id);
        }
        $this->render(array("id_space" => $idSpace, "lang" => $lang, "htmlForm" => $form->getHtml($lang)));
    }
}

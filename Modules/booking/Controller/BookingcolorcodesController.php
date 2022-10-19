<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

require_once 'Modules/booking/Model/BkColorCode.php';
require_once 'Modules/booking/Controller/BookingsettingsController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BookingcolorcodesController extends BookingsettingsController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $idSpace, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        // get the user list
        $colorModel = new BkColorCode();
        $colorTable = $colorModel->getForSpace($idSpace);
        $bkcodes = $colorTable;
        for ($i = 0 ; $i < count($colorTable) ; $i++) {
            $colorTable[$i]["who_can_use"] = CoreTranslator::Translate_status_from_id($lang, $colorTable[$i]["who_can_use"]);
        }

        $table = new TableView();

        $table->setTitle(BookingTranslator::Color_codes($lang), 3);
        $table->addLineEditButton("bookingcolorcodeedit/".$idSpace);
        $table->addDeleteButton("bookingcolorcodedelete/".$idSpace);
        $table->setColorIndexes(array("color" => "color"));

        $tableContent = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang),
            "color" => CoreTranslator::Color($lang),
            "who_can_use" => BookingTranslator::WhoCanUse($lang)
        );

        $tableHtml = $table->view($colorTable, $tableContent);

        return $this->render(array("data" => ["bkcodes" => $bkcodes], "id_space" => $idSpace, "tableHtml" => $tableHtml, "lang" => $lang));
    }

    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $idSpace, $_SESSION["id_user"]);

        $model = new BkColorCode();
        if ($id > 0) {
            $data = $model->getColorCode($idSpace, $id);
        } else {
            $data = $model->getDefault();
        }
        $lang = $this->getLanguage();
        $form = new Form($this->request, "editActionForm");
        $form->setTitle(BookingTranslator::Edit_color_code($lang), 3);
        $form->addText("name", CoreTranslator::Name($lang), false, $data["name"]);
        $form->addColor("color", BookingTranslator::Color($lang), false, $data["color"]);
        $form->addColor("text", BookingTranslator::Text($lang), false, $data["text"]);
        $roles = CoreSpace::roles($lang);
        $form->addSelect("who_can_use", BookingTranslator::WhoCanUse($lang), $roles["names"], $roles["ids"], $data["display_order"]);
        $form->addNumber("display_order", BookingTranslator::Display_order($lang), false, $data["display_order"]);

        $todo = $this->request->getParameterNoException('redirect');
        $validationUrl = "bookingcolorcodeedit/".$idSpace."/".$id;
        if ($todo) {
            $validationUrl .= "?redirect=todo";
        }

        $form->setValidationButton(CoreTranslator::Save($lang), $validationUrl);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "bookingcolorcodes/".$idSpace);

        if ($form->check()) {
            $newID = $model->editColorCode($id, $form->getParameter("name"), $form->getParameter("color"), $form->getParameter("text"), $idSpace, $form->getParameter("display_order"));
            $model->setColorWhoCanUse($idSpace, $newID, $form->getParameter("who_can_use"));

            $_SESSION["flash"] = BookingTranslator::Item_created("colorcode", $lang);
            $_SESSION["flashClass"] = "success";

            if ($todo) {
                return $this->redirect("spaceadminedit/" . $idSpace, ["showTodo" => true]);
            } else {
                return $this->redirect("bookingcolorcodes/".$idSpace, [], ['bkcode' => ['id' => $newID]]);
            }
        }
        $formHtml = $form->getHtml($lang);

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "formHtml" => $formHtml));
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $idSpace, $_SESSION["id_user"]);

        $model = new BkColorCode();
        $model->delete($idSpace, $id);
        $this->redirect("bookingcolorcodes/".$idSpace);
    }
}

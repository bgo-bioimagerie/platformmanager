<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

require_once 'Modules/booking/Model/BkColorCode.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingcolorcodesController extends CoresecureController {

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

        // get the user list
        $colorModel = new BkColorCode();
        $colorTable = $colorModel->getForSpace($id_space);
        for($i = 0 ; $i < count($colorTable) ; $i++){
            $colorTable[$i]["who_can_use"] = CoreTranslator::Translate_status_from_id($lang, $colorTable[$i]["who_can_use"]);
        }

        $table = new TableView ();

        $table->setTitle(BookingTranslator::Color_codes($lang), 3);
        $table->addLineEditButton("bookingcolorcodeedit/".$id_space);
        $table->addDeleteButton("bookingcolorcodedelete/".$id_space);
        $table->setColorIndexes(array("color" => "color"));

        $tableContent = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang),
            "color" => CoreTranslator::Color($lang),
            "who_can_use" => BookingTranslator::WhoCanUse($lang)
        );

        $tableHtml = $table->view($colorTable, $tableContent);

        $this->render(array("id_space" => $id_space, "tableHtml" => $tableHtml, "lang" => $lang));
    }
    
    public function editAction($id_space, $id){
        
        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);
        
        $model = new BkColorCode();
        if ($id > 0){
            $data = $model->getColorCode($id_space, $id);
        }
        else{
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
        
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingcolorcodeedit/".$id_space."/".$id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "bookingcolorcodes/".$id_space);
        $form->setButtonsWidth(3, 9);
        
        if ($form->check()){
            
            $newID = $model->editColorCode($id, $form->getParameter("name"), $form->getParameter("color"), $form->getParameter("text"), $id_space, $form->getParameter("display_order"));
            $model->setColorWhoCanUse($id_space, $newID, $form->getParameter("who_can_use"));
            $this->redirect("bookingcolorcodes/".$id_space);
        }
        $formHtml = $form->getHtml($lang);
        
        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $formHtml));
    }
    
    public function deleteAction($id_space, $id){
        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);
        
        $model = new BkColorCode();
        $model->delete($id_space, $id);
        $this->redirect("bookingcolorcodes/".$id_space);
    }

}

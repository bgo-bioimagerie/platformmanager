<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

require_once 'Modules/booking/Model/BkColorCode.php';
require_once 'Modules/ecosystem/Model/EcSite.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingcolorcodesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->checkAuthorizationMenu("bookingsettings");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction() {

        $lang = $this->getLanguage();

        // get sort action
        $sortentry = "id";
        if ($this->request->isParameterNotEmpty('actionid')) {
            $sortentry = $this->request->getParameter("actionid");
        }

        // get the user list
        $colorModel = new BkColorCode();
        $colorTable = $colorModel->getColorCodes($sortentry);

        $table = new TableView ();

        $table->setTitle(BookingTranslator::Color_codes($lang));
        $table->addLineEditButton("bookingcolorcodeedit");
        $table->addDeleteButton("bookingcolorcodedelete");
        $table->setColorIndexes(array("color" => "color"));

        $tableContent = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang),
            "color" => CoreTranslator::Color($lang)
        );

        $tableHtml = $table->view($colorTable, $tableContent);

        $this->render(array("tableHtml" => $tableHtml, "lang" => $lang));
    }
    
    public function editAction($id){
        
        $model = new BkColorCode();
        if ($id > 0){
            $data = $model->getColorCode($id);
        }
        else{
            $data = $model->getDefault();
        }
        $lang = $this->getLanguage();
        $form = new Form($this->request, "editActionForm");
        $form->setTitle(BookingTranslator::Edit_color_code($lang));
        $form->addText("name", CoreTranslator::Name($lang), false, $data["name"]);
        $form->addColor("color", BookingTranslator::Color($lang), false, $data["color"]);
        $form->addColor("text", BookingTranslator::Text($lang), false, $data["text"]);
        $form->addNumber("display_order", BookingTranslator::Display_order($lang), false, $data["display_order"]);
        
        $modelSite = new EcSite();
        $sites = $modelSite->getUserAdminSites($_SESSION["id_user"]);
        $allSites = $modelSite->getAll("name");
        $choices = array(); $choicesid = array();
        foreach($allSites as $s){
            $choices[] = $s["name"];
            $choicesid[] = $s["id"];
        }
        
        if(count($sites) == 1){
            $form->addHidden("id_site", $sites[0]["id"]);
        }
        else if (count($sites) > 1){
            $form->addSelect("id_site", EcosystemTranslator::Site($lang), $choices, $choicesid, $data["id_site"]);
        }
        else{
            throw new Exception(EcosystemTranslator::NeedToBeSiteManager($lang));
        }
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingcolorcodeedit/".$id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "bookingcolorcodes");
        $form->setButtonsWidth(3, 9);
        
        if ($form->check()){
            
            $model->editColorCode($id, $form->getParameter("name"), $form->getParameter("color"), $form->getParameter("text"), $form->getParameter("id_site"), $form->getParameter("display_order"));
            $this->redirect("bookingcolorcodes");
        }
        $formHtml = $form->getHtml($lang);
        
        $this->render(array("lang" => $lang, "formHtml" => $formHtml));
    }
    
    public function deleteAction($id){
        
        $model = new BkColorCode();
        $model->delete($id);
        $this->redirect("bookingcolorcodes");
    }

}

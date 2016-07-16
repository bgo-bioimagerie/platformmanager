<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkBookingTableCSS.php';
require_once 'Modules/resources/Model/ReArea.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingdisplayController extends CoresecureController {

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
        
        $modelArea = new ReArea();
        $areas = $modelArea->getAll("name");
        
        $table = new TableView();
        $table->setTitle(BookingTranslator::Display($lang));
        $table->addLineEditButton("bookingdisplayedit");
        
        $headers = array("name" => CoreTranslator::Name($lang));
        
        $tableHtml = $table->view($areas, $headers);
        
        $this->render(array("lang" => $lang, "tableHtml" => $tableHtml));
    }
    
    public function editAction($id){
        
        $lang = $this->getLanguage();
        
        $modelCSS = new BkBookingTableCSS();
        $data = $modelCSS->getAreaCss($id);
        
        $modelArea = new ReArea();
        $name = $modelArea->getName($id);
        
        $form = new Form($this->request, "bookingschedulingedit");
        $form->setTitle(BookingTranslator::Display($lang) . ": " . $name);
        $form->addColor("header_background", BookingTranslator::Header_Color($lang), false, $data["header_background"]);
        $form->addColor("header_color", BookingTranslator::Header_Text($lang), false, $data["header_color"]);
        $form->addNumber("header_font_size", BookingTranslator::Header_font_size($lang), false, $data["header_font_size"]);
        $form->addNumber("resa_font_size", BookingTranslator::Resa_font_size($lang), false, $data["resa_font_size"]);
        $form->addNumber("header_height", BookingTranslator::Header_height($lang), false, $data["header_height"]);
        $form->addNumber("line_height", BookingTranslator::Line_height($lang), false, $data["line_height"]);
       
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingdisplayedit/".$id);
        $form->setColumnsWidth(3, 9);
        $form->setButtonsWidth(3, 9);
        if ($form->check()){
            $modelCSS->setAreaCss($id, $this->request->getParameter("header_background"), 
                    $this->request->getParameter("header_color"),
                    $this->request->getParameter("header_font_size"),
                    $this->request->getParameter("resa_font_size"),
                    $this->request->getParameter("header_height"),
                    $this->request->getParameter("line_height"));
            $this->redirect("bookingdisplayedit/".$id);
             
        }
        $this->render(array("lang" => $lang, "htmlForm" => $form->getHtml($lang) ));
        
    }
}

<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkScheduling.php';
require_once 'Modules/booking/Model/BkColorCode.php';
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/core/Model/CoreStatus.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingschedulingController extends CoresecureController {

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
        
        $modelArea = new ReArea();
        $areas = $modelArea->getForSpace($id_space);
        
        $table = new TableView();
        $table->setTitle(BookingTranslator::Scheduling($lang), 3);
        $table->addLineEditButton("bookingschedulingedit/".$id_space);
        
        $headers = array("name" => CoreTranslator::Name($lang));
        
        $tableHtml = $table->view($areas, $headers);
        
        $this->render(array("id_space" => $id_space, "lang" => $lang, "tableHtml" => $tableHtml));
    }
    
    public function editAction($id_space, $id) {
        // That to avoid some confusions between id_rearea and bkScheduling['id']
        $id_rearea = $id;
        
        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        $modelArea = new ReArea();
        $area = $modelArea->get($id_space, $id_rearea);
        $name = $area['name'];
        
        $modelScheduling = new BkScheduling();
        $bkScheduling = $modelScheduling->getByReArea($id_space, $id_rearea);

        if (!$bkScheduling) {
            $bkScheduling = $modelScheduling->getDefault();
            $bkScheduling["id_space"] = $id_space;
            $bkScheduling["id_rearea"] = $id_rearea;
        }

        $form = new Form($this->request, "bookingschedulingedit");
        $form->setTitle(BookingTranslator::Edit_scheduling($lang) . ": " . $name, 3);
        $form->addChoicesList(BookingTranslator::Availables_days($lang),
                BookingTranslator::DaysList($lang), 
                array("is_monday", "is_tuesday", "is_wednesday", "is_thursday", "is_friday", "is_saturday", "is_sunday"),
                array($bkScheduling["is_monday"], $bkScheduling["is_tuesday"], $bkScheduling["is_wednesday"], $bkScheduling["is_thursday"], $bkScheduling["is_friday"], $bkScheduling["is_saturday"], $bkScheduling["is_sunday"])
                );
        
        $dc = array(); $dcid = array();
        for ($d = 0 ; $d < 25 ; $d++){
            $dc[] = $d . "h";
            $dcid[] = $d;
        }
        $form->addSelect("day_begin", BookingTranslator::Day_beginning($lang), $dc, $dcid, $bkScheduling["day_begin"]);
        $form->addSelect("day_end", BookingTranslator::Day_end($lang), $dc, $dcid, $bkScheduling["day_end"]);
        $form->addSelect("size_bloc_resa", BookingTranslator::Booking_size_bloc($lang), array("15min", "30min", "1h"), array(900, 1800, 3600), $bkScheduling["size_bloc_resa"]);
        $form->addSelect("booking_time_scale", BookingTranslator::Booking_time_scale($lang), array(BookingTranslator::Minutes($lang), BookingTranslator::Hours($lang), BookingTranslator::Days($lang)), array(1, 2, 3), $bkScheduling["booking_time_scale"]);
        $form->addSelect("resa_time_setting", BookingTranslator::The_user_specify($lang), array(BookingTranslator::the_booking_duration($lang), BookingTranslator::the_date_time_when_reservation_ends($lang)), array(1, 2), $bkScheduling["resa_time_setting"]);
        
        $modelColor = new BkColorCode();
        $colors = $modelColor->getForSpace($id_space);
        
        $cc = array(); $ccid = array();
        foreach($colors as $color){
            $cc[] = $color["name"];
            $ccid[] = $color["id"];
        }
        $form->addSelect("default_color_id", BookingTranslator::Default_color($lang), $cc, $ccid, $bkScheduling["default_color_id"]);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingschedulingedit/".$id_space."/".$id_rearea);
        $form->setColumnsWidth(3, 9);
        $form->setButtonsWidth(3, 9);

        if ($form->check()) {
            $modelScheduling->edit($id_space, $bkScheduling['id_rearea'],
                    $this->request->getParameterNoException("is_monday"), 
                    $this->request->getParameterNoException("is_tuesday"), 
                    $this->request->getParameterNoException("is_wednesday"), 
                    $this->request->getParameterNoException("is_thursday"), 
                    $this->request->getParameterNoException("is_friday"), 
                    $this->request->getParameterNoException("is_saturday"), 
                    $this->request->getParameterNoException("is_sunday"), 
                    $this->request->getParameter("day_begin"), 
                    $this->request->getParameter("day_end"), 
                    $this->request->getParameter("size_bloc_resa"), 
                    $this->request->getParameter("booking_time_scale"), 
                    $this->request->getParameter("resa_time_setting"), 
                    $this->request->getParameter("default_color_id"));
               
            $this->redirect("bookingschedulingedit/".$id_space."/".$id_rearea);
            return;
        }
         
        $this->render(array("id_space" => $id_space, "lang" => $lang, "htmlForm" => $form->getHtml($lang) ));
        
    }
}

<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Controller/CorespaceController.php';


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingsettingsController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("bookingsettings");
        $_SESSION["openedNav"] = "bookingsettings";
    }

    public function sideMenu() {
        $id_space = $this->args['id_space'];
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("bookingsettings", $id_space);
       
        $dataView = [
            'id_space' => $id_space,
            'title' => BookingTranslator::Booking($lang),
            'glyphicon' => $menuInfo['icon'],
            'bgcolor' => $menuInfo['color'],
            'color' => $menuInfo['txtcolor'] ?? '',
            'Calendar_View' => BookingTranslator::Calendar_View($lang),
            'Scheduling' => BookingTranslator::Scheduling($lang),
            'Display' =>BookingTranslator::Display($lang),
            'Accessibilities' =>BookingTranslator::Accessibilities($lang),
            'Nightwe' =>BookingTranslator::Nightwe($lang),
            'Color_codes' =>BookingTranslator::Color_codes($lang),
            'Additional_info' =>BookingTranslator::Additional_info($lang),
            'SupplementariesInfo' =>BookingTranslator::SupplementariesInfo($lang),
            'Packages' =>BookingTranslator::Packages($lang),
            'Quantities' =>BookingTranslator::Quantities($lang),
            'booking' =>BookingTranslator::booking($lang),
            'Block_Resouces' =>BookingTranslator::Block_Resouces($lang),
            'Restrictions' =>BookingTranslator::Restrictions($lang)
        ];
        return $this->twig->render("Modules/booking/View/Booking/navbar.twig", $dataView);
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        
        $lang = $this->getLanguage();
        $this->render(array("id_space" => $id_space, "lang" => $lang));
    }
}

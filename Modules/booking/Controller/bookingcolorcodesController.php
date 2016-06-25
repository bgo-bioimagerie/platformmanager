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
class bookingcolorcodesController extends CoresecureController {

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

        $table->setTitle(SyTranslator::color_codes($lang));
        $table->addLineEditButton("bookingcolorcodesedit");
        $table->addDeleteButton("bookingcolorcodesdelete");
        $table->setColorIndexes(array("color" => "color"));

        $tableContent = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang),
            "color" => CoreTranslator::Color($lang)
        );

        $tableHtml = $table->view($colorTable, $tableContent);

        $navBar = $this->navBar();
        $this->generateView(array(
            'tableHtml' => $tableHtml
        ));

        $this->render(array("lang" => $lang));
    }

}

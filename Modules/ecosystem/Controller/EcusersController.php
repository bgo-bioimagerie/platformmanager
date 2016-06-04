<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/ecosystem/Model/EcUser.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';

/**
 * Manage the units (each user belongs to an unit)
 * 
 * @author sprigent
 *
 */
class EcusersController extends CoresecureController {

    /**
     * User model object
     */
    private $userModel;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->userModel = new EcUser ();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction($active = "") {

        if ($active == "") {
            if (isset($_SESSION["users_lastvisited"])) {
                $active = $_SESSION["users_lastvisited"];
            } else {
                $active = "active";
            }
        }

        $lang = $this->getLanguage();

        // get the user list
        $usersArray = array();
        $title = CoreTranslator::Users($lang);
        if ($active == "active") {
            $usersArray = $this->userModel->getActiveUsersInfo(1);
        } else {
            $usersArray = $this->userModel->getActiveUsersInfo(0);
            $title = CoreTranslator::Unactive_Users($lang);
        }

        for ($i = 0; $i < count($usersArray); $i++) {
            // is responsible
            if ($usersArray[$i]["is_responsible"] == 1) {
                $usersArray[$i]["is_responsible"] = CoreTranslator::yes($lang);
            } else {
                $usersArray[$i]["is_responsible"] = CoreTranslator::no($lang);
            }

            // convention
            if ($usersArray[$i]['date_convention'] == "0000-00-00") {
                $convTxt = CoreTranslator::Not_signed($lang);
            } else {
                $convTxt = "" . CoreTranslator::Signed_the($lang)
                        . " " . CoreTranslator::dateFromEn($usersArray[$i]['date_convention'], $lang) . "";
            }
            $usersArray[$i]['convention'] = $convTxt;

            // dates
            $usersArray[$i]['date_created'] = CoreTranslator::dateFromEn($usersArray[$i]['date_created'], $lang);
            $usersArray[$i]['date_last_login'] = CoreTranslator::dateFromEn($usersArray[$i]['date_last_login'], $lang);

            $respsIds = $this->userModel->getUserResponsibles($usersArray[$i]['id']);
            $usersArray[$i]['resp_name'] = "";
            for ($j = 0; $j < count($respsIds); $j++) {
                $usersArray[$i]['resp_name'] .= $this->userModel->getUserFUllName($respsIds[$j][0]);
                if ($j < count($respsIds) - 1) {
                    $usersArray[$i]['resp_name'] .= ", ";
                }
            }
        }

        //print_r($usersArray);
        $modelCoreConfig = new CoreConfig();
        $authorisations_location = $modelCoreConfig->getParam("sy_authorisations_location");

        $table = new TableView();

        $table->setTitle($title);
        $table->addLineEditButton("ecusersedit");
        $table->addDeleteButton("ecusersdelete");
        if ($authorisations_location == 2) {
            $table->addLineButton("Sygrrifauthorisations/userauthorizations", "id", CoreTranslator::Authorizations($lang));
        }
        $tableContent = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang),
            "firstname" => CoreTranslator::Firstname($lang),
            "login" => CoreTranslator::Login($lang),
            "email" => CoreTranslator::Email($lang),
            "tel" => CoreTranslator::Phone($lang),
            "unit" => CoreTranslator::Unit($lang),
            "resp_name" => CoreTranslator::Responsible($lang),
            "status" => CoreTranslator::Status($lang),
            "is_responsible" => CoreTranslator::is_responsible($lang),
        );


        if ($modelCoreConfig->getParam("visible_date_convention") > 0) {
            $tableContent["convention"] = CoreTranslator::Convention($lang);
        }
        if ($modelCoreConfig->getParam("visible_date_created") > 0) {
            $tableContent["date_created"] = CoreTranslator::User_from($lang);
        }
        if ($modelCoreConfig->getParam("visible_date_last_login") > 0) {
            $tableContent["date_last_login"] = CoreTranslator::Last_connection($lang);
        }
        if ($modelCoreConfig->getParam("visible_date_end_contract") > 0) {
            $tableContent["date_end_contract"] = CoreTranslator::Date_end_contract($lang);
        }
        if ($modelCoreConfig->getParam("visible_source") > 0) {
            $tableContent["source"] = CoreTranslator::Source($lang);
        }


        $tableHtml = $table->view($usersArray, $tableContent);
        $this->render(array(
            'lang' => $lang,
            'tableHtml' => $tableHtml
                ), "index");
    }

    public function activeAction() {
        $_SESSION["users_lastvisited"] = "active";
        $this->indexAction("active");
    }
    
    public function unactiveAction() {
        $_SESSION["users_lastvisited"] = "unactive";
        $this->indexAction("unactive");
    }

}

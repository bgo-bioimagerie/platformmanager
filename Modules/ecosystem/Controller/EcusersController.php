<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/ecosystem/Model/EcUser.php';
require_once 'Modules/ecosystem/Model/EcUnit.php';
require_once 'Modules/ecosystem/Model/EcResponsible.php';
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
        //$this->checkAuthorizationMenu("users/institutions");
        $this->userModel = new EcUser ();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction($id_space, $letter = "A", $active = "") {

        if( $letter == ""){
            $letter = "A";
        }
        
        $this->checkAuthorizationMenuSpace("users/institutions", $id_space, $_SESSION["id_user"]);

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
            $usersArray = $this->userModel->getActiveUsersInfoLetter($letter, 1);
        } else {
            $usersArray = $this->userModel->getActiveUsersInfoLetter($letter, 0);
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

        //$table->setTitle($title);

        $modelCoreSpace = new CoreSpace();
        $isBooking = $modelCoreSpace->isSpaceMenu($id_space, "booking");
        if ($isBooking) {
            $table->addLineButton("bookingauthorisations/" . $id_space, "id", CoreTranslator::Authorizations($lang));
        }

        $table->addLineEditButton("ecusersedit/" . $id_space);
        $table->addDeleteButton("ecusersdelete/" . $id_space);
        $table->setFixedColumnsNum(5);
        if ($authorisations_location == 2) {
            $table->addLineButton("Sygrrifauthorisations/userauthorizations", "id", CoreTranslator::Authorizations($lang));
        }
        $tableContent = array(
            "name" => CoreTranslator::Name($lang),
            "firstname" => CoreTranslator::Firstname($lang),
            "login" => CoreTranslator::Login($lang),
            "email" => CoreTranslator::Email($lang),
            "phone" => CoreTranslator::Phone($lang),
            "unit" => CoreTranslator::Unit($lang),
            "resp_name" => CoreTranslator::Responsible($lang),
            "status" => CoreTranslator::Status($lang),
            "is_responsible" => CoreTranslator::is_responsible($lang),
            "id" => "ID",
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
            'id_space' => $id_space,
            'tableHtml' => $tableHtml,
            'active' => $active,
            'letter' => $letter,
                ), "indexAction");
    }

    public function activeAction($id_space, $letter = "A") {
        
        $_SESSION["users_lastvisited"] = "active";
        $this->indexAction($id_space, $letter, "active");
    }

    public function unactiveAction($id_space, $letter = "A") {
        $_SESSION["users_lastvisited"] = "unactive";
        $this->indexAction($id_space, $letter, "unactive");
    }

    public function editAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("users/institutions", $id_space, $_SESSION["id_user"]);

// get info
        if ($id > 0) {
            $user = $this->userModel->getInfo($id);
        } else {
            $user = $this->userModel->getDefault($id);
        }

// lang
        $lang = $this->getLanguage();

// units
        $modelUnit = new EcUnit();
        $units = $modelUnit->getUnits("name");
        $choicesU = array();
        $choicesidU = array();
        foreach ($units as $unit) {
            $choicesU[] = $unit["name"];
            $choicesidU[] = $unit["id"];
        }

// resp
        $modelResp = new EcResponsible();
        $resps = $modelResp->responsibleSummaries("name");
        $choicesR = array();
        $choicesidR = array();
        foreach ($resps as $resp) {
            $choicesR[] = $resp["name"] . " " . $resp["firstname"];
            $choicesidR[] = $resp["id"];
        }

// status
        $modelStatus = new CoreStatus();
        $status = $modelStatus->statusIDName();
        $choicesS = array();
        $choicesidS = array();
        foreach ($status as $statu) {
            $choicesS[] = $statu["name"];
            $choicesidS[] = $statu["id"];
        }

// form
// build the form
        $form = new Form($this->request, "ecusersedit");
        $form->setTitle(CoreTranslator::Edit_User($lang), 3);
        $form->addHidden("id", $user["id"]);
        $form->addText("name", EcosystemTranslator::Name($lang), true, $user["name"]);
        $form->addText("firstname", EcosystemTranslator::Firstname($lang), true, $user["firstname"]);
        $form->addText("login", EcosystemTranslator::Login($lang), true, $user["login"]);
        if ($id == 0) {
            $form->addPassword("pwd", EcosystemTranslator::Password($lang), false);
            $form->addPassword("confirm", EcosystemTranslator::Confirm($lang), false);
        }
        $form->addEmail("email", EcosystemTranslator::Email($lang), false, $user["email"]);
        $form->addText("phone", EcosystemTranslator::Phone($lang), false, $user["phone"]);
        $form->addSelect("unit", EcosystemTranslator::Unit($lang), $choicesU, $choicesidU, $user["id_unit"]);

        $formAdd = new FormAdd($this->request, "userformadd");

        $resps = array();
        foreach ($user["id_resps"] as $idResp) {
            $resps[] = $idResp["id_resp"];
        }
        $formAdd->addSelect("responsibles", EcosystemTranslator::Responsible($lang), $choicesR, $choicesidR, $resps);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form->setFormAdd($formAdd, CoreTranslator::Responsible($lang));

        $form->addSelect("is_responsible", EcosystemTranslator::is_responsible($lang), array(EcosystemTranslator::yes($lang), EcosystemTranslator::no($lang)), array(1, 0), $user["is_responsible"]);
        $form->addSelect("id_status", EcosystemTranslator::Status($lang), $choicesS, $choicesidS, $user["status_id"]);
        $form->addDate("date_convention", EcosystemTranslator::Date_convention($lang), false, CoreTranslator::dateFromEn($user["date_convention"], $lang));

        if ($user["convention_url"] != "") {
            $form->addDownloadButton(EcosystemTranslator::Convention($lang), $user["convention_url"]);
        }
        $form->addUpload("convention_url", EcosystemTranslator::Convention($lang));

        $form->addDate("date_end_contract", EcosystemTranslator::Date_end_contract($lang), false, CoreTranslator::dateFromEn($user["date_end_contract"], $lang));

        if ($id > 0) {
            $form->addSelect("is_active", CoreTranslator::Is_user_active($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $user["is_active"]);
            $form->addText("source", CoreTranslator::Source($lang), false, $user["source"], "disabled");
        }

        $form->setValidationButton(CoreTranslator::Ok($lang), "ecusersedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "ecusers/" . $id_space);
        $form->setColumnsWidth(2, 9);
        $form->setButtonsWidth(2, 9);

        $script = "";
        if ($form->check()) {

            if ($id > 0) {
                $this->userModel->edit($id, $this->request->getParameter("name"), $this->request->getParameter("firstname"), $this->request->getParameter("login"), $this->request->getParameter("email"), $this->request->getParameter("phone"), $this->request->getParameter("unit"), $this->request->getParameter("is_responsible"), $this->request->getParameter("id_status"), $this->request->getParameter("date_convention"), $this->request->getParameter("date_end_contract"), $this->request->getParameter("is_active")
                );
                $modelResp = new EcResponsible();
                $modelResp->setResponsibles($id, $this->request->getParameter("responsibles"));
                $this->uploadConvention($id);
                $this->redirect("ecusers/" . $id_space);
            } else {
                $modelUser = new CoreUser();
                if ($modelUser->isLogin($this->request->getParameter('login'))) {
                    $script .= '<script language="javascript">';
                    $script .= 'alert("' . CoreTranslator::LoginAlreadyExists($lang) . '")';
                    $script .= '</script>';
                } else if ($this->request->getParameter('pwd') != $this->request->getParameter('confirm')) {
                    $script .= '<script language="javascript">';
                    $script .= 'alert("' . CoreTranslator::TheTwoPasswordAreDifferent($lang) . '")';
                    $script .= '</script>';
                } else {

                    $password = $this->request->getParameter("pwd");
                    if ($password == "") {
                        $password = $this->randomPassword();
                    }

                    $id = $this->userModel->add(
                            $this->request->getParameter("name"), $this->request->getParameter("firstname"), $this->request->getParameter("login"), $password, $this->request->getParameter("email"), $this->request->getParameter("phone"), $this->request->getParameter("unit"), $this->request->getParameter("is_responsible"), $this->request->getParameter("id_status"), $this->request->getParameter("date_convention"), $this->request->getParameter("date_end_contract")
                    );
                    $modelResp = new EcResponsible();
                    $modelResp->setResponsibles($id, $this->request->getParameter("responsibles"));
                    $this->uploadConvention($id);
                    $this->redirect("ecusers/" . $id_space);
                }
            }
        }
// set the view
        $formHtml = $form->getHtml();
// view
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'script' => $script,
            'id' => $id,
            'formHtml' => $formHtml
        ));
    }

    function randomPassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }

    public function uploadConvention($id) {
        $target_dir = "data/ecosystem/convention/";
        if ($_FILES["convention_url"]["name"] != "") {
            $ext = pathinfo($_FILES["convention_url"]["name"], PATHINFO_EXTENSION);
            Upload::uploadFile($target_dir, "convention_url", $id . "." . $ext);
            $this->userModel->setConventionUrl($id, $target_dir . $id . "." . $ext);
        }
    }

    public function changepwdAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("users/institutions", $id_space, $_SESSION["id_user"]);

        $user = $this->userModel->getInfo($id);

        // generate view
        $lang = $this->getLanguage();
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'user' => $user
        ));
    }

    public function changepwdqAction($id_space) {

        $this->checkAuthorizationMenuSpace("users/institutions", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $id = $this->request->getParameter("id");
        $pwd = $this->request->getParameter("pwd");
        $pwdc = $this->request->getParameter("pwdc");

        if ($pwd == $pwdc) {
            // this database
            $modelUser = new CoreUser();
            $modelUser->changePwd($id, $pwd);
        } else {
            throw new Exception(CoreTranslator::TheTwoPasswordAreDifferent($lang));
        }

        // generate view
        $this->render(array("lang" => $lang, "id_space" => $id_space
        ));
    }

    public function deleteAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("users/institutions", $id_space, $_SESSION["id_user"]);

        $this->userModel->delete($id);
        $this->redirect("ecusers/" . $id_space);
    }

    public function exportrespAction($id_space) {

        $lang = $this->getLanguage();

        $form = new Form($this->request, "exportRespFrom");
        $form->setTitle(EcosystemTranslator::ExportResponsibles($lang), 3);

        $choicesid = array(0, 1, 2);
        $choices = array(CoreTranslator::All($lang), CoreTranslator::Active($lang), CoreTranslator::Unactive($lang));
        $form->addSelect("exporttype", EcosystemTranslator::Responsible($lang), $choices, $choicesid);

        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Ok($lang), "ecexportresponsible/" . $id_space);

        if ($form->check()) {
            $exportType = $this->request->getParameter("exporttype");
            $this->userModel->exportResponsible($exportType);
            return;
        }

        $this->render(array("lang" => $lang, "id_space" => $id_space, "formHtml" => $form->getHtml($lang)));
    }

}

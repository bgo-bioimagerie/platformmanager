<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';
require_once 'Framework/Download.php';
require_once 'Framework/Email.php';
require_once 'Framework/Constants.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Controller/CorespaceaccessController.php';
require_once 'Modules/clients/Controller/ClientsuseraccountsController.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/booking/Model/BkAuthorization.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/resources/Model/ReVisa.php';
require_once 'Modules/core/Model/CoreSpaceUser.php';
require_once 'Modules/core/Model/CoreInstalledModules.php';
require_once 'Modules/core/Model/CorePendingAccount.php';
require_once 'Modules/core/Model/CoreSpaceAccessOptions.php';
require_once 'Modules/core/Controller/CorespaceController.php';
require_once 'Modules/core/Model/CoreTranslator.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class CorespaceuserController extends CorespaceaccessController {

    // space access section
    public function editAction($id_space, $id_user) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $origin = ["page" => json_encode($this->request->getParameterNoException("origin"))];
        if ($origin['page'] == "") {
            $origin = false;
        }

        $spaceAccessForm = $this->generateSpaceAccessForm($id_space, $id_user);

        $clientsUsersCTRL = new ClientsuseraccountsController($this->request);
        $clientsUserForm = $clientsUsersCTRL->generateClientsUserForm($id_space, $id_user);
        $clientsUsertableHtml = $clientsUsersCTRL->generateClientsUserTable($id_space, $id_user);

        $generatedBkAuth = $this->generateBkAuthTable($id_space, $id_user);
        $bkAuthTableHtml = $generatedBkAuth['bkTableHtml'];
        $bkAuthData = $generatedBkAuth['data'];
        $bkAuthAddForm = $this->generateBkAuthAddForm($id_space, $id_user);

        if ($spaceAccessForm->check()) {
            $this->validateSpaceAccessForm($id_space, $id_user, $spaceAccessForm);
        }
        if ($clientsUserForm->check()) {
            $clientsUsersCTRL->validateClientsUserform($id_space, $id_user, $clientsUserForm);
        }

        if ($bkAuthAddForm->check()) {
            $this->validateBkAuthAddForm($id_space, $id_user, $bkAuthAddForm);
        }

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);
        $dataView = [
            'id_space' => $id_space,
            'id_user' => $id_user,
            'lang' => $lang,
            "space" => $space,
            'origin' => json_encode($origin),
            'spaceAccessForm' => $spaceAccessForm->getHtml($lang),
            'clientsUserForm' => $clientsUserForm->getHtml($lang),
            "clientsUserTable" => $clientsUsertableHtml,
            "bkAuthTable" => $bkAuthTableHtml,
            "bkAuthData" => $bkAuthData,
            "bkAuthAddForm" => $bkAuthAddForm->getHtml($lang)
        ];
        return $this->render($dataView, "editAction");
    }


    /////////////////////////////////////////
    ///// BOOKINGAUTHORIZATIONS SECTION /////
    /////////////////////////////////////////

    protected function generateBkAuthTable($id_space, $id_user) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelResources = new ReCategory();
        $resources = $modelResources->getBySpace($id_space);
        $modelUser = new CoreUser();
        $userName = $modelUser->getUserFUllName($id_user);
        $modelAuth = new BkAuthorization();

        $data = array();
        foreach ($resources as $r) {
            if ($modelAuth->hasAuthorization($id_space, $r["id"], $id_user)) {
                $authInfo = $modelAuth->getLastActiveAuthorization($id_space, $r["id"], $id_user);
                $authorised = CoreTranslator::yes($lang);
                $authorised_color = "#32CD32";
                $date_authorized = CoreTranslator::dateFromEn($authInfo["date"], $lang);
            } else {
                $authorised = CoreTranslator::no($lang);
                $authorised_color = "#FF8C00";
                $date_authorized = "";
            }
            $data[] = array(
                "id" => $r["id"] . "_" . $id_user,
                "resource_category" => $r["name"],
                "date_authorised" => $date_authorized,
                "authorised" => $authorised,
                "authorised_color" => $authorised_color
            );
        }

        $headers = array(
            "resource_category" => ResourcesTranslator::Category($lang),
            "authorised" => BookingTranslator::Authorized($lang),
            "date_authorised" => CoreTranslator::Date($lang)
        );

        $table = new TableView("bkAuth");
        $table->setTitle(BookingTranslator::Authorisations_for($lang) . " " . $userName, 3);
        $table->setColorIndexes(array("authorised" => "authorised_color"));
        $table->addLineButton("bookingauthorisationshist/" . $id_space, "id", BookingTranslator::History($lang));

        return ["bkTableHtml" => $table->view($data, $headers), "data" => $data];
    }

    protected function generateBkAuthAddForm($id_space, $id_user) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelUser = new CoreUser();
        $userName = $modelUser->getUserFUllName($id_user);
        $modelReCategories = new ReCategory();
        $categories = $modelReCategories->getBySpace($id_space);
        $CategoryList = $modelReCategories->getForList($id_space);    
        $defaultCategoryId = $categories[0]['id'];
        $modelVisa = new ReVisa();
        $visa_select = $modelVisa->getForListByCategory($id_space, $defaultCategoryId);

        if (empty($visa_select['ids'])) {
            $_SESSION['flash'] = BookingTranslator::VisaNeeded($lang);
        }

        $form = new Form($this->request, "authorisationAddForm");
        $form->setTitle(BookingTranslator::Authorisations_for($lang) . ": " . $userName);
        $form->addText("user", CoreTranslator::User(), false, $userName, "disabled");
        $form->addSelectMandatory("resource", ResourcesTranslator::Category(), $CategoryList['names'], $CategoryList['ids'], $defaultCategoryId);
        $form->addSelectMandatory("visa_id", BookingTranslator::Visa($lang), $visa_select["names"], $visa_select["ids"]);
        $form->addDate("date", BookingTranslator::DateActivation($lang), true);
        $form->setValidationButton(CoreTranslator::Save($lang), "corespaceuseredit/" . $id_space . "/" . $id_user, ["origin" => "bookingaccess"]);

        return $form;
    }

    protected function validateBkAuthAddForm($id_space, $id_user, $form) {
        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelAuth = new BkAuthorization();
            $modelAuth->add(
                $id_space,
                $id_user,
                $form->getParameter("resource") /* stands for category id */,
                $form->getParameter("visa_id"),
                CoreTranslator::dateToEn($form->getParameter("date"), $lang)
            );
            $_SESSION["flash"] = ResourcesTranslator::AuthorisationAdded($lang);
            $_SESSION["flashClass"] = "success";
            $this->redirect("corespaceuseredit" ."/" . $id_space . "/" . $id_user, ["origin" => "bookingaccess"]);
    }

    public function bkAuthHistoryAction($id_space, $id) {

        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $idArray = explode("_", $id);
        $id_resource_category = intval($idArray[0]);
        if (!is_int($id_resource_category)) {
            throw new PfmParamException("id resource category is not an int");
        }
        $id_user = intval($idArray[1]);
        if (!is_int($id_user)) {
            throw new PfmParamException("id user is not an int");
        }
        $modelUser = new CoreUser();
        $userName = $modelUser->getUserFUllName($id_user);

        $modelCategory = new ReCategory();

        $table = new TableView("bkAuthHistory");
        $table->setTitle(BookingTranslator::Authorisations_history_for($lang) . " " . $userName);
        $table->setColorIndexes(array("active" => "authorised_color"));

        $table->addLineEditButton("bookingauthorisationsedit/" . $id_space, "id");

        $modelVisa = new BkAuthorization();
        $data = $modelVisa->getForResourceAndUser($id_space, $id_resource_category, $id_user);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["user"] = $modelUser->getUserFUllName($data[$i]["user_id"]);
            $data[$i]["resource_category"] = $modelCategory->getName($id_space, $data[$i]["resource_id"]);
            if ($data[$i]["is_active"] == 1) {
                $data[$i]["authorised_color"] = "#32CD32";
                $data[$i]["active"] = CoreTranslator::yes($lang);
            } else {
                $data[$i]["authorised_color"] = "#FF8C00";
                $data[$i]["active"] = CoreTranslator::no($lang);
            }
        }

        $headers = array(
            "user" => CoreTranslator::User($lang),
            "resource_category" => ResourcesTranslator::Category($lang),
            "date" => BookingTranslator::DateActivation($lang),
            "date_desactivation" => BookingTranslator::DateDesactivation($lang),
            "active" => ResourcesTranslator::IsActive($lang),
        );

        $tableHtml = $table->view($data, $headers);

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        $this->render(array(
            "lang" => $lang,
            "id_space" => $id_space,
            'tableHtml' => $tableHtml,
            'space' => $space
        ));
    }

    public function editBkAuthAction($id_space, $id) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelAuth = new BkAuthorization();
        $data = $modelAuth->get($id_space, $id);

        $modelUser = new CoreUser();
        $userName = $modelUser->getUserFUllName($data["user_id"]);


        $modelResourcesCategories = new ReCategory();
        // $categoryName = $modelResourcesCategories->getName($data["resource_id"]);
        $id_resource_category = $data["resource_id"];
        $recat = $modelResourcesCategories->get($id_space, $id_resource_category);
        $categoryName = $recat['name'];

        $modelVisa = new ReVisa();
        $visa_select = $modelVisa->getForListByCategory($id_space, $data["resource_id"]);

        $form = new Form($this->request, "authorisationAddForm");
        $form->setTitle(BookingTranslator::Authorisations_for($lang) . ": " . $userName);
        $form->addText("user", CoreTranslator::User(), false, $userName, "disabled");
        $form->addText("resource", BookingTranslator::Resource(), false, $categoryName, "disabled");


        $form->addSelect("visa_id", BookingTranslator::Visa($lang), $visa_select["names"], $visa_select["ids"], $data["visa_id"]);
        $form->addDate("date", BookingTranslator::DateActivation($lang), true, $data["date"], $lang);

        $form->addDate("date_desactivation", BookingTranslator::DateDesactivation($lang), false, $data["date_desactivation"]);
        $form->addSelect("is_active", ResourcesTranslator::IsActive($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $data["is_active"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "bookingauthorisationsedit/" . $id_space . "/" . $id);

        if ($form->check()) {

            $modelAuth = new BkAuthorization();
            $modelAuth->set($id_space, $id, $data["user_id"], $data["resource_id"], $form->getParameter("visa_id"), CoreTranslator::dateToEn($form->getParameter("date"), $lang), CoreTranslator::dateToEn($form->getParameter("date_desactivation"), $lang), $form->getParameter("is_active"));


            $this->redirect("bookingauthorisations/" . $id_space . "/" . $data["user_id"]);
            return;
        }

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'formHtml' => $form->getHtml($lang),
            'space' => $space
        ));
    }

}

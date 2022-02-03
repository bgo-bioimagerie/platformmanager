<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/booking/Model/BkAuthorization.php';

require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/resources/Model/ReVisa.php';

require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingauthorisationsController extends CoresecureController {

    public function indexAction($id_space, $id_user) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        $form = $this->generateBkAuthAddForm($id_space, $id_user, "bookingauthorisations");
        $generatedBkAuth = $this->generateBkAuthTable($id_space, $id_user, "bookingauthorisations");
        $tableHtml = $generatedBkAuth['bkTableHtml'];
        $bkAuthData = $generatedBkAuth['data'];

        if ($form->check()) {
            $this->validateBkAuthAddForm($id_space, $id_user, $form, "bookingauthorisations");
        }

        return $this->render(array(
            "lang" => $lang,
            "id_space" => $id_space,
            'tableHtml' => $tableHtml,
            "formHtml" => $form->getHtml($lang),
            'space' => $space,
            'data' => $bkAuthData
        ));
    }

    public function generateBkAuthTable($id_space, $id_user, $controller) {
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

        $route = ($controller === "bookingauthorisations") ? $controller . "hist" : $controller;

        $table = new TableView("bkAuth");
        $table->setTitle(BookingTranslator::Authorisations_for($lang) . " " . $userName, 3);
        $table->setColorIndexes(array("authorised" => "authorised_color"));
        if ($controller === "bookingauthorisations") {
            $table->addLineButton($route . "/" . $id_space, "id", BookingTranslator::History($lang));    
        }
        return ["bkTableHtml" => $table->view($data, $headers), "data" => $data];
    }

    public function generateBkAuthAddForm($id_space, $id_user, $controller) {
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
        $form->setTitle(BookingTranslator::Add_authorisation_for($lang) . ": " . $userName);
        $form->addText("user", CoreTranslator::User(), false, $userName, "disabled");
        $form->addSelectMandatory("resource", ResourcesTranslator::Category(), $CategoryList['names'], $CategoryList['ids'], $defaultCategoryId);
        $form->addSelectMandatory("visa_id", BookingTranslator::Visa($lang), $visa_select["names"], $visa_select["ids"]);
        $form->addDate("date", BookingTranslator::DateActivation($lang), true);
        $form->setValidationButton(CoreTranslator::Save($lang), $controller . "/" . $id_space . "/" . $id_user, ["origin" => "bookingaccess"]);

        return $form;
    }

    public function validateBkAuthAddForm($id_space, $id_user, $form, $controller) {
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
            $this->redirect($controller ."/" . $id_space . "/" . $id_user, ["origin" => "bookingaccess"]);
    }

    public function historyAction($id_space, $id) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $idArray = explode("_", $id);
        $id_category = intval($idArray[0]);
        if (!is_int($id_category)) {
            throw new PfmParamException("id resource category is not an int");
        }
        $id_user = intval($idArray[1]);
        if (!is_int($id_user)) {
            throw new PfmParamException("id user is not an int");
        }

        $tableHtml = $this->generateHistoryTable($id_space, $id_user, $id_category);
        $form = $this->generateEditForm($id_space, $id_user, $id_category, "bookingauthorisations");

        if ($form->check()) {
            $this->validateEditForm($id_space, $id, $form);
        }

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        $this->render(array(
            "lang" => $lang,
            "id_space" => $id_space,
            'formHtml' => $form->getHtml($lang),
            'tableHtml' => $tableHtml,
            'space' => $space
        ));
    }

    public function generateHistoryTable($id_space, $id_user, $id_category, $allCategories = false) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelUser = new CoreUser();
        $userName = $modelUser->getUserFUllName($id_user);

        $modelCategory = new ReCategory();
    
        if ($allCategories) {
            $categories = $modelCategory->getBySpace($id_space);
            $categoryName = "";
        } else {
            $categoryName = " / " . $modelCategory->getName($id_space, $id_category);
        }

        $table = new TableView();
        $table->setTitle(BookingTranslator::Authorisations_history_for($lang) . " " . $userName . $categoryName);
        $table->setColorIndexes(array("active" => "authorised_color"));
        $table->addLineEditButton("bookingauthorisationsedit/" . $id_space, "id");

        $modelVisa = new BkAuthorization();
        
        if ($allCategories) {
            $visas_array = array();
            for ($i = 0; $i < count($categories); $i++) {
                array_push($visas_array, $modelVisa->getForResourceAndUser($id_space, $categories[$i]['id'], $id_user));  
            }
            $data = array_merge(...$visas_array);
        } else {
            $data = $modelVisa->getForResourceAndUser($id_space, $id_category, $id_user);
        }
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

        return $table->view($data, $headers);
    }

    public function generateEditForm($id_space, $id_user, $id_category) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelAuth = new BkAuthorization();
        $data = $modelAuth->getForResourceAndUser($id_space, $id_category, $id_user)[0] ?? null;

        $form = new Form($this->request, "authorisationAddForm");

        if ($data) {
            $modelUser = new CoreUser();
            $userName = $modelUser->getUserFUllName($id_user);

            $modelResourcesCategories = new ReCategory();
            $id_resource_category = $data["resource_id"];
            $recat = $modelResourcesCategories->get($id_space, $id_resource_category);
            $categoryName = $recat['name'];

            $modelVisa = new ReVisa();
            $visa_select = $modelVisa->getForListByCategory($id_space, $data["resource_id"]);

            $form->setTitle(BookingTranslator::Authorisations_for($lang) . ": " . $userName);
            $form->addText("user", CoreTranslator::User(), false, $userName, readonly:true);
            $form->addText("resource", BookingTranslator::Resource(), false, $categoryName, readonly:true);
            $form->addSelect("visa_id", BookingTranslator::Visa($lang), $visa_select["names"], $visa_select["ids"], $data["visa_id"]);
            $form->addDate("date", BookingTranslator::DateActivation($lang), true, $data["date"], $lang);
            $form->addDate("date_desactivation", BookingTranslator::DateDesactivation($lang), false, $data["date_desactivation"]);
            $form->addSelect("is_active", ResourcesTranslator::IsActive($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $data["is_active"]);
            
            $form->setValidationButton(CoreTranslator::Save($lang), "bookingauthorisationshist" . "/" . $id_space . "/" . $data['id']);
        }
        return $form;
    }

    public function validateEditForm($id_space, $id, $form) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelAuth = new BkAuthorization();
        // We keep initial user and resource ids since it can't and shouldn't be modified in the edit action
        $bkAuth = $modelAuth->get($id_space, $id);
        $modelAuth->set(
            $id_space,
            $id,
            $bkAuth['user_id'],
            $bkAuth['resource_id'],
            $form->getParameter("visa_id"),
            CoreTranslator::dateToEn($form->getParameter("date"), $lang),
            CoreTranslator::dateToEn($form->getParameter("date_desactivation"), $lang),
            $form->getParameter("is_active")
        );

        $_SESSION["flash"] = BookingTranslator::Modifications_have_been_saved($lang);
        $_SESSION["flashClass"] = "success";
        $redirectionUrl = "corespaceuseredit/" . $id_space . "/" . /* $bkAuth['resource_id'] . "_" .  */$bkAuth['user_id'];
        $this->redirect($redirectionUrl, ["origin" => "bookingaccesshistory"]);
    }

    
    public function editAction($id_space, $id) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelAuth = new BkAuthorization();
        $data = $modelAuth->get($id_space, $id);

        $form = $this->generateEditForm($id_space, $data["user_id"], $data['resource_id']);

        if ($form->check()) {
            $this->validateEditForm($id_space, $id, $form);
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

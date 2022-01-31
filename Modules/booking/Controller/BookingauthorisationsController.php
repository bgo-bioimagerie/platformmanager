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

    public function indexAction($id_space, $id) {

        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // get all the resources
        $modelResources = new ReCategory();
        $resources = $modelResources->getBySpace($id_space);

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        // user name
        $modelUser = new CoreUser();
        $userName = $modelUser->getUserFUllName($id);


        // model Authorization
        $modelAuth = new BkAuthorization();

        // visas
        $data = array();
        foreach ($resources as $r) {
            if ($modelAuth->hasAuthorization($id_space, $r["id"], $id)) {
                $authInfo = $modelAuth->getLastActiveAuthorization($id_space, $r["id"], $id);
                $authorised = CoreTranslator::yes($lang);
                $authorised_color = "#32CD32";
                $date_authorized = CoreTranslator::dateFromEn($authInfo["date"], $lang);
            } else {
                $authorised = CoreTranslator::no($lang);
                $authorised_color = "#FF8C00";
                $date_authorized = "";
            }
            $data[] = array(
                "id" => $r["id"] . "_" . $id,
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

        $table = new TableView();
        $table->setTitle(BookingTranslator::Authorisations_for($lang) . " " . $userName, 3);
        $table->setColorIndexes(array("authorised" => "authorised_color"));

        $table->addLineButton("bookingauthorisationsadd/" . $id_space, "id", CoreTranslator::Add($lang));
        $table->addLineButton("bookingauthorisationshist/" . $id_space, "id", BookingTranslator::History($lang));

        $tableHtml = $table->view($data, $headers);

        return $this->render(array(
            "lang" => $lang,
            "id_space" => $id_space,
            'tableHtml' => $tableHtml,
            'space' => $space,
            'data' => ['bkauthorizations' => $data]
        ));
    }

    public function historyAction($id_space, $id) {

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

        //$recat = $modelCategory->get($id_space, $id_resource_category);

        $table = new TableView();
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

    public function addAction($id_space, $id) {
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


        $modelResourcesCategories = new ReCategory();

        $recat = $modelResourcesCategories->get($id_space, $id_resource_category);
        $categoryName = $recat['name'];


        $modelVisa = new ReVisa();
        $visa_select = $modelVisa->getForListByCategory($id_space, $id_resource_category);
        if (empty($visa_select['ids'])) {
            $_SESSION['flash'] = BookingTranslator::VisaNeeded($lang);
        }


        $form = new Form($this->request, "authorisationAddForm");
        $form->setTitle(BookingTranslator::Authorisations_for($lang) . ": " . $userName);
        $form->addText("user", CoreTranslator::User(), false, $userName, "disabled");
        $form->addText("resource", BookingTranslator::Resource(), false, $categoryName, "disabled");


        $form->addSelectMandatory("visa_id", BookingTranslator::Visa($lang), $visa_select["names"], $visa_select["ids"]);
        $form->addDate("date", BookingTranslator::DateActivation($lang), true);

        $form->setValidationButton(CoreTranslator::Save($lang), "bookingauthorisationsadd/" . $id_space . "/" . $id);

        if ($form->check()) {
            $modelAuth = new BkAuthorization();
            $modelAuth->add($id_space, $id_user, $id_resource_category, $form->getParameter("visa_id"), CoreTranslator::dateToEn($form->getParameter("date"), $lang)
            );

            $this->redirect("bookingauthorisations/" . $id_space . "/" . $id_user);
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

    public function editAction($id_space, $id) {
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

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
require_once 'Modules/core/Controller/CorespaceadminController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BookingauthorisationsController extends CoresecureController
{
    public function indexAction($idSpace, $idUser)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($idSpace);

        $form = $this->generateBkAuthAddForm($idSpace, $idUser, "bookingauthorisations", $lang);
        $generatedBkAuth = $this->generateBkAuthTable($idSpace, $idUser, "bookingauthorisations", $lang);
        $tableHtml = $generatedBkAuth['bkTableHtml'];
        $bkAuthData = $generatedBkAuth['data'];

        if ($form->check()) {
            $this->validateBkAuthAddForm(
                $idSpace,
                $idUser,
                $form->getParameter("resource") /* stands for category id */,
                $form->getParameter("visa_id"),
                $form->getParameter("date")
            );
        }

        return $this->render(array(
            "lang" => $lang,
            "id_space" => $idSpace,
            'tableHtml' => $tableHtml,
            "formHtml" => $form->getHtml($lang),
            'space' => $space,
            'data' => ['bkauthorizations' => $bkAuthData]
        ));
    }

    public function generateBkAuthTable($idSpace, $idUser, $controller, $lang)
    {
        $modelResources = new ReCategory();
        $resources = $modelResources->getBySpace($idSpace);
        $modelUser = new CoreUser();
        $userName = $modelUser->getUserFullName($idUser);
        $modelAuth = new BkAuthorization();

        $data = array();
        foreach ($resources as $r) {
            if ($modelAuth->hasAuthorization($idSpace, $r["id"], $idUser)) {
                $authInfo = $modelAuth->getLastActiveAuthorization($idSpace, $r["id"], $idUser);
                $authorised = CoreTranslator::yes($lang);
                $authorised_color = "#32CD32";
                $date_authorized = CoreTranslator::dateFromEn($authInfo["date"], $lang);
            } else {
                $authorised = CoreTranslator::no($lang);
                $authorised_color = "#FF8C00";
                $date_authorized = "";
            }
            $data[] = array(
                "id" => $r["id"] . "_" . $idUser,
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
        if ($controller === "bookingauthorisations") {
            $table->addLineButton($controller . "hist" . "/" . $idSpace, "id", BookingTranslator::History($lang));
        }
        return ["bkTableHtml" => $table->view($data, $headers), "data" => $data];
    }

    public function generateBkAuthAddForm($idSpace, $idUser, $controller, $lang, $todo=false)
    {
        $modelUser = new CoreUser();
        $userName = $modelUser->getUserFullName($idUser);
        $modelReCategories = new ReCategory();
        $categories = $modelReCategories->getBySpace($idSpace);
        $CategoryList = $modelReCategories->getForList($idSpace);
        $defaultCategoryId = $categories[0]['id'];
        $modelVisa = new ReVisa();
        $visa_select = $modelVisa->getForListByCategory($idSpace, $defaultCategoryId);

        if (empty($visa_select['ids'])) {
            $_SESSION['flash'] = BookingTranslator::VisaNeeded($lang);
        }

        $form = new Form($this->request, "authorisationAddForm");
        $form->setTitle(BookingTranslator::Add_authorisation_for($lang) . ": " . $userName);
        $form->addText("user", CoreTranslator::User(), false, $userName, "disabled");
        $form->addSelectMandatory("resource", ResourcesTranslator::Category(), $CategoryList['names'], $CategoryList['ids'], $defaultCategoryId);
        $form->addSelectMandatory("visa_id", BookingTranslator::Visa($lang), $visa_select["names"], $visa_select["ids"]);
        $form->addDate("date", BookingTranslator::DateActivation($lang), true);

        $validationUrl = $controller . "/". $idSpace."/". $idUser;
        if ($todo) {
            $validationUrl .= "?redirect=todo";
        }

        $form->setValidationButton(CoreTranslator::Save($lang), $validationUrl, ["origin" => "bookingaccess"]);

        return $form;
    }

    public function validateBkAuthAddForm($idSpace, $idUser, $id_category, $id_visa, $date)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelAuth = new BkAuthorization();
        $modelAuth->add(
            $idSpace,
            $idUser,
            $id_category,
            $id_visa,
            CoreTranslator::dateToEn($date, $lang)
        );

        $_SESSION["flash"] = ResourcesTranslator::AuthorisationAdded($lang);
        $_SESSION["flashClass"] = "success";
    }

    public function historyAction($idSpace, $id)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $idArray = explode("_", $id);
        $id_category = intval($idArray[0]);
        if (!is_int($id_category)) {
            throw new PfmParamException("id resource category is not an int");
        }
        $idUser = intval($idArray[1]);
        if (!is_int($idUser)) {
            throw new PfmParamException("id user is not an int");
        }

        $tableHtml = $this->generateHistoryTable($idSpace, $idUser, $id_category);
        $form = $this->generateEditForm($idSpace, $idUser, $id_category);

        if ($form->check()) {
            $this->validateEditForm(
                $idSpace,
                $id,
                $form->getParameter("visa_id"),
                $form->getParameter("date"),
                $form->getParameter("date_desactivation"),
                $form->getParameter("is_active"),
                $lang
            );
        }

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($idSpace);

        $this->render(array(
            "lang" => $lang,
            "id_space" => $idSpace,
            'formHtml' => $form->getHtml($lang),
            'tableHtml' => $tableHtml,
            'space' => $space
        ));
    }

    public function generateHistoryTable($idSpace, $idUser, $id_category, $allCategories = false)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelUser = new CoreUser();
        $userName = $modelUser->getUserFullName($idUser);

        $modelCategory = new ReCategory();

        if ($allCategories) {
            $categories = $modelCategory->getBySpace($idSpace);
            $categoryName = "";
        } else {
            $categoryName = " / " . $modelCategory->getName($idSpace, $id_category);
        }



        $table = new TableView();
        $table->setTitle(BookingTranslator::Authorisations_history_for($lang) . " " . $userName . $categoryName);
        $table->setColorIndexes(array("active" => "authorised_color"));
        $table->addLineEditButton("bookingauthorisationsedit/" . $idSpace, "id");
        $table->addDeleteButton("bookingauthorisationsdelete/" . $idSpace . "/" . $idUser, deleteNameIndex: "delete_text");

        $modelVisa = new BkAuthorization();

        if ($allCategories) {
            $visas_array = array();
            for ($i = 0; $i < count($categories); $i++) {
                array_push($visas_array, $modelVisa->getForResourceAndUser($idSpace, $categories[$i]['id'], $idUser));
            }
            $data = array_merge(...$visas_array);
        } else {
            $data = $modelVisa->getForResourceAndUser($idSpace, $id_category, $idUser);
        }
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["user"] = $modelUser->getUserFullName($data[$i]["user_id"]);
            $data[$i]["resource_category"] = $modelCategory->getName($idSpace, $data[$i]["resource_id"]);
            if ($data[$i]["is_active"] == 1) {
                $data[$i]["authorised_color"] = "#32CD32";
                $data[$i]["active"] = CoreTranslator::yes($lang);
            } else {
                $data[$i]["authorised_color"] = "#FF8C00";
                $data[$i]["active"] = CoreTranslator::no($lang);
            }
            $data[$i]["delete_text"] = $data[$i]["resource_category"] . " authorisation";
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

    public function generateEditForm($idSpace, $idUser, $id_category, $data = null)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelAuth = new BkAuthorization();
        if ($data === null) {
            $data = $modelAuth->getForResourceAndUser($idSpace, $id_category, $idUser)[0] ?? null;
        }
        $form = new Form($this->request, "authorisationAddForm");

        if ($data) {
            $modelUser = new CoreUser();
            $userName = $modelUser->getUserFullName($idUser);

            $modelResourcesCategories = new ReCategory();
            $id_resource_category = $data["resource_id"];
            $recat = $modelResourcesCategories->get($idSpace, $id_resource_category);
            $categoryName = $recat['name'];

            $modelVisa = new ReVisa();
            $visa_select = $modelVisa->getForListByCategory($idSpace, $data["resource_id"]);

            $form->setTitle(BookingTranslator::Authorisations_for($lang) . ": " . $userName);
            $form->addText("user", CoreTranslator::User(), false, $userName, readonly:true);
            $form->addText("resource", BookingTranslator::Resource(), false, $categoryName, readonly:true);
            $form->addSelect("visa_id", BookingTranslator::Visa($lang), $visa_select["names"], $visa_select["ids"], $data["visa_id"]);
            $form->addDate("date", BookingTranslator::DateActivation($lang), true, $data["date"], $lang);
            $form->addDate("date_desactivation", BookingTranslator::DateDesactivation($lang), false, $data["date_desactivation"]);
            $form->addSelect("is_active", ResourcesTranslator::IsActive($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $data["is_active"]);

            $form->setValidationButton(CoreTranslator::Save($lang), "bookingauthorisationshist" . "/" . $idSpace . "/" . $data['id']);
        }
        return $form;
    }

    public function validateEditForm($idSpace, $id, $visa_id, $date, $date_desactivation, $is_active, $lang)
    {
        $modelAuth = new BkAuthorization();
        // We keep initial user and resource ids since it can't and shouldn't be modified in the edit action
        $bkAuth = $modelAuth->get($idSpace, $id);
        $modelAuth->set(
            $idSpace,
            $id,
            $bkAuth['user_id'],
            $bkAuth['resource_id'],
            $visa_id,
            CoreTranslator::dateToEn($date, $lang),
            CoreTranslator::dateToEn($date_desactivation, $lang),
            $is_active
        );
        $_SESSION["flash"] = BookingTranslator::Modifications_have_been_saved($lang);
        $_SESSION["flashClass"] = "success";
        $redirectionUrl = "corespaceuseredit/" . $idSpace . "/" . /* $bkAuth['resource_id'] . "_" .  */$bkAuth['user_id'];
        $this->redirect($redirectionUrl, ["origin" => "bookingaccesshistory"]);
    }


    public function editAction($idSpace, $id)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelAuth = new BkAuthorization();
        $data = $modelAuth->get($idSpace, $id);

        $form = $this->generateEditForm($idSpace, $data["user_id"], $data['resource_id'], $data);

        if ($form->check()) {
            $this->validateEditForm(
                $idSpace,
                $id,
                $form->getParameter("visa_id"),
                $form->getParameter("date"),
                $form->getParameter("date_desactivation"),
                $form->getParameter("is_active"),
                $lang
            );
        }

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($idSpace);

        $this->render(array(
            'id_space' => $idSpace,
            'lang' => $lang,
            'formHtml' => $form->getHtml($lang),
            'space' => $space
        ));
    }

    /**
     * Remove a bk_authorization
     */
    public function deleteAction($idSpace, $idUser, $id)
    {
        // security
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);

        // remove bk_authorization
        $modelAuth = new BkAuthorization();
        $modelAuth->delete($idSpace, $id);

        $this->redirect("corespaceuseredit/" . $idSpace . "/" . $idUser . "?origin=bookingaccesshistory");
    }
}

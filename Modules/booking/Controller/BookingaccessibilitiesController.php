<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/booking/Controller/BookingsettingsController.php';
/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BookingaccessibilitiesController extends BookingsettingsController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $idSpace, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        $form = new Form($this->request, "bookingaccessibilities");
        $form->setTitle(BookingTranslator::Accessibilities($lang));

        $model = new BkAccess();

        $choicesid = array(1, 2, 3, 4);
        $choices = array();
        $choices[] = BookingTranslator::User($lang);
        $choices[] = BookingTranslator::Authorized_users($lang);
        $choices[] = BookingTranslator::Manager($lang);
        $choices[] = BookingTranslator::Admin($lang);

        $modelResources = new ResourceInfo();
        $resources = $modelResources->getForSpace($idSpace);

        if (empty($resources)) {
            $_SESSION['flash'] = ResourcesTranslator::Resource_Needed($lang);
            $_SESSION['flashClass'] = "warning";
        }

        $bkaccess = [];
        foreach ($resources as $resource) {
            $accessId = $model->getAccessId($idSpace, $resource["id"]);
            $bkaccess[] = ['resource' => $resource['id'], 'bkaccess' => $accessId];
            $form->addSelect("r_" . $resource["id"], $resource["name"], $choices, $choicesid, $accessId);
        }

        $todo = $this->request->getParameterNoException('redirect');
        $validationUrl = "bookingaccessibilities/".$idSpace;
        if ($todo) {
            $validationUrl .= "?redirect=todo";
        }

        $form->setValidationButton(CoreTranslator::Save($lang), $validationUrl);

        if ($form->check()) {
            $bkaccess = [];
            foreach ($resources as $resource) {
                $id_access = $this->request->getParameterNoException("r_" . $resource["id"]);
                $model->set($idSpace, $resource["id"], $id_access);
                $bkaccess[] = ['resource' => $resource['id'], 'bkaccess' => $accessId];
            }

            $_SESSION["flash"] = BookingTranslator::Item_created("access", $lang);
            $_SESSION["flashClass"] = "success";

            if ($todo) {
                return $this->redirect("spaceadminedit/" . $idSpace, ["showTodo" => true]);
            } else {
                return $this->redirect("bookingaccessibilities/".$idSpace, [], ["bkaccess" => $bkaccess]);
            }
        }

        // view
        $formHtml = $form->getHtml($lang);
        return $this->render(array("data" => ["bkaccess" => $bkaccess],"id_space" => $idSpace, "formHtml" => $formHtml, "lang" => $lang));
    }
}

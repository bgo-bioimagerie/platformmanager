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
require_once 'Modules/booking/Controller/BookingauthorisationsController.php';
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

    
    // TODO: call bkauth history from this controller 
    // TODO: filter settings with activated modules
    // TODO: design interface
    // TODO: remove corespaceaccessusers user's buttons
    // TODO: display flash messages ?


    // space access section
    public function editAction($id_space, $id_user) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $origin = ["page" => json_encode($this->request->getParameterNoException("origin"))];
        if ($origin['page'] === "bkauthhistory") {
            $idArray = explode("_", $id_user);
            $id_category = intval($idArray[0]);
            if (!is_int($id_category)) {
                throw new PfmParamException("id resource category is not an int");
            }
            $id_user = intval($idArray[1]);
            if (!is_int($id_user)) {
                throw new PfmParamException("id user is not an int");
            }
        } else if ($origin['page'] == "") {
            $origin = false;
        }

        $spaceAccessForm = $this->generateSpaceAccessForm($id_space, $id_user);

        $clientsUsersCTRL = new ClientsuseraccountsController($this->request);
        $clientsUserForm = $clientsUsersCTRL->generateClientsUserForm($id_space, $id_user);
        $clientsUsertableHtml = $clientsUsersCTRL->generateClientsUserTable($id_space, $id_user);

        $bookingAuthCTRL = new BookingauthorisationsController($this->request);
        $bkAuthAddForm = $bookingAuthCTRL->generateBkAuthAddForm($id_space, $id_user, "corespaceuseredit");
        $generatedBkAuth = $bookingAuthCTRL->generateBkAuthTable($id_space, $id_user, "corespaceuseredit");
        $bkAuthTableHtml = $generatedBkAuth['bkTableHtml'];
        $bkAuthData = $generatedBkAuth['data'];

        $bkHistoryTableHtml = "";
        if (strpos($id_user, "_") !== false) {
            $origin['page'] = "bkauthhistory";
            $idArray = explode("_", $id_user);
            $id_category = intval($idArray[0]);
            if (!is_int($id_category)) {
                throw new PfmParamException("id resource category is not an int");
            }
            $id_user = intval($idArray[1]);
            if (!is_int($id_user)) {
                throw new PfmParamException("id user is not an int");
            }
            $bkHistoryTableHtml = $bookingAuthCTRL->generateHistoryTable($id_space, $id_user, $id_category, "corespaceuseredit");
            $bkHistoryForm = $bookingAuthCTRL->generateEditForm($id_space, $id_user, $id_category, "corespaceuseredit");
        }
        $bkHistoryFormHtml = isset($bkHistoryForm) ? $bkHistoryForm->getHtml($lang) : "no bkHistoryForm";
        
        if ($spaceAccessForm->check()) {
            $this->validateSpaceAccessForm($id_space, $id_user, $spaceAccessForm);
        }
        if ($clientsUserForm->check()) {
            $clientsUsersCTRL->validateClientsUserform($id_space, $id_user, $clientsUserForm);
        }

        if ($bkAuthAddForm->check()) {
            $bookingAuthCTRL->validateBkAuthAddForm($id_space, $id_user, $bkAuthAddForm, "corespaceuseredit");
        }

        /* if ($bkHistoryForm->check()) {
            $bookingAuthCTRL->validateEditForm($id_space, $id_user, $bkHistoryForm);
        } */

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
            "bkAuthAddForm" => $bkAuthAddForm->getHtml($lang),
            "bkHistoryTable" => $bkHistoryTableHtml,
            "bkHistoryForm" => $bkHistoryFormHtml
        ];
        return $this->render($dataView, "editAction");
    }


    /////////////////////////////////////////
    ///// BOOKINGAUTHORIZATIONS SECTION /////
    /////////////////////////////////////////

    public function bkAuthHistoryAction($id_space, $id) {
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
        $bookingAuthCTRL = new BookingauthorisationsController($this->request);
        $tableHtml = $bookingAuthCTRL->generateHistoryTable($id_space, $id_user, $id_category, "corespaceuseredit");
        $form = $bookingAuthCTRL->generateEditForm($id_space, $id_user, $id_category, "corespaceuseredit");

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

}

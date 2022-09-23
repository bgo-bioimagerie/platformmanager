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
    public function editAction($id_space, $id_user) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $origin = ["page" => $this->request->getParameterNoException("origin")];
        $todo = ($this->request->getParameterNoException("redirect") === "todo")
            ? true : false;
        $modelOptions = new CoreSpaceAccessOptions();
        $options = array_reverse($modelOptions->getAll($id_space));
        $modules = array_map(function($option) {
            return $option['module'];
        }, $options);

        $spaceAccessForm = $this->generateSpaceAccessForm($id_space, $id_user, $todo);
        $spaceAccessFormHtml = $spaceAccessForm->getHtml($lang);
        if ($spaceAccessForm->check()) {
            $this->validateSpaceAccessForm($id_space, $id_user, $spaceAccessForm, $todo);
            if ($todo) {
                return $this->redirect("spaceadminedit/" . $id_space, ["showTodo" => true]);
            }
            $origin['page'] = 'spaceaccess';
        }
        $forms = ['space' => ['forms' => [$spaceAccessFormHtml], 'show' => 1]];
        $btnNames = ['space' => CoreTranslator::Space_access($lang)];

        $clientsUserFormHtml = "";
        $clientsUsertableHtml = "";

        $modelSpace = new CoreSpace();
        $moduleRole = CoreSpace::$INACTIF;
        if (in_array('clients', $modules)) {
            $moduleRole = $modelSpace->getSpaceMenusRole($id_space, 'clients');
        }
        if ($moduleRole) {
            $clientsUsersCTRL = new ClientsuseraccountsController($this->request);
            $clientsUserForm = $clientsUsersCTRL->generateClientsUserForm($id_space, $id_user, $todo);
            $clientsUserFormHtml = $clientsUserForm->getHtml($lang);
            if ($clientsUserForm->check()) {
                $clientsUsersCTRL->validateClientsUserform($id_space, $id_user, $clientsUserForm->getParameter("id_client"), $todo);
                if ($todo) {
                    return $this->redirect("spaceadminedit/" . $id_space, ["showTodo" => true]);
                }
                $origin['page'] = 'clientsuser';
            }
            $clientsUsertableHtml = $clientsUsersCTRL->generateClientsUserTable($id_space, $id_user, $todo);
            $forms['clients'] = ['forms'  => [$clientsUserFormHtml, $clientsUsertableHtml], 'show' => 0];
            $btnNames['clients'] = ClientsTranslator::clientsuseraccounts($lang);
        }

        $bkAuthAddFormHtml = "";
        $bkAuthTableHtml = "";
        $bkHistoryTableHtml = "";
        $moduleRole = CoreSpace::$INACTIF;
        if (in_array('booking', $modules)) {
            $moduleRole = $modelSpace->getSpaceMenusRole($id_space, 'booking');
        }
        if ($moduleRole) {
            $bookingAuthCTRL = new BookingauthorisationsController($this->request);
            $bkAuthAddForm = $bookingAuthCTRL->generateBkAuthAddForm($id_space, $id_user, "corespaceuseredit", $lang, $todo);
            $bkAuthAddFormHtml = $bkAuthAddForm->getHtml($lang);
            if ($bkAuthAddForm->check()) {
                $bookingAuthCTRL->validateBkAuthAddForm(
                    $id_space,
                    $id_user,
                    $bkAuthAddForm->getParameter("resource"), /* stands for category id */
                    $bkAuthAddForm->getParameter("visa_id"),
                    $bkAuthAddForm->getParameter("date"),
                    $todo
                );
                if ($todo) {
                    return $this->redirect("spaceadminedit/" . $id_space, ["showTodo" => true]);
                }
                $origin['page'] = 'bookingaccess';
            }
            $bkAuth = $bookingAuthCTRL->generateBkAuthTable($id_space, $id_user, "corespaceuseredit", $lang);
            $bkAuthTableHtml = $bkAuth['bkTableHtml'];
            $bkHistoryTableHtml = $bookingAuthCTRL->generateHistoryTable($id_space, $id_user, null, true);
            $forms['booking'] = ['forms' => [$bkAuthAddFormHtml, $bkAuthTableHtml, $bkHistoryTableHtml], 'show' => 0];
            $btnNames['booking'] = BookingTranslator::bookingauthorisations($lang);
        }
        
        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);
        $dataView = [
            'id_space' => $id_space,
            'id_user' => $id_user,
            'data' => [
                "space" => $space,
                'origin' => $origin,
                'options' => $options,
                'btnsNames' => $btnNames
            ],
            'forms' => $forms,
            'lang' => $lang
        ];
        return $this->render($dataView, "editAction");
    }

}

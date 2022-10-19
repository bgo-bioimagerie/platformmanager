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
class CorespaceuserController extends CorespaceaccessController
{
    public function editAction($idSpace, $idUser)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $origin = ["page" => $this->request->getParameterNoException("origin")];
        $todo = ($this->request->getParameterNoException("redirect") === "todo")
            ? true : false;
        $modelOptions = new CoreSpaceAccessOptions();
        $options = array_reverse($modelOptions->getAll($idSpace));
        $modules = array_map(function ($option) {
            return $option['module'];
        }, $options);

        $spaceAccessForm = $this->generateSpaceAccessForm($idSpace, $idUser, $todo);
        $spaceAccessFormHtml = $spaceAccessForm->getHtml($lang);
        if ($spaceAccessForm->check()) {
            $this->validateSpaceAccessForm($idSpace, $idUser, $spaceAccessForm, $todo);
            if ($todo) {
                return $this->redirect("spaceadminedit/" . $idSpace, ["showTodo" => true]);
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
            $moduleRole = $modelSpace->getSpaceMenusRole($idSpace, 'clients');
        }
        if ($moduleRole) {
            $clientsUsersCTRL = new ClientsuseraccountsController($this->request);
            $clientsUserForm = $clientsUsersCTRL->generateClientsUserForm($idSpace, $idUser, $todo);
            $clientsUserFormHtml = $clientsUserForm->getHtml($lang);
            if ($clientsUserForm->check()) {
                $clientsUsersCTRL->validateClientsUserform($idSpace, $idUser, $clientsUserForm->getParameter("id_client"), $todo);
                if ($todo) {
                    return $this->redirect("spaceadminedit/" . $idSpace, ["showTodo" => true]);
                }
                $origin['page'] = 'clientsuser';
            }
            $clientsUsertableHtml = $clientsUsersCTRL->generateClientsUserTable($idSpace, $idUser, $todo);
            $forms['clients'] = ['forms'  => [$clientsUserFormHtml, $clientsUsertableHtml], 'show' => 0];
            $btnNames['clients'] = ClientsTranslator::clientsuseraccounts($lang);
        }

        $bkAuthAddFormHtml = "";
        $bkAuthTableHtml = "";
        $bkHistoryTableHtml = "";
        $moduleRole = CoreSpace::$INACTIF;
        if (in_array('booking', $modules)) {
            $moduleRole = $modelSpace->getSpaceMenusRole($idSpace, 'booking');
        }
        if ($moduleRole) {
            $bookingAuthCTRL = new BookingauthorisationsController($this->request);
            $bkAuthAddForm = $bookingAuthCTRL->generateBkAuthAddForm($idSpace, $idUser, "corespaceuseredit", $lang, $todo);
            $bkAuthAddFormHtml = $bkAuthAddForm->getHtml($lang);
            if ($bkAuthAddForm->check()) {
                $bookingAuthCTRL->validateBkAuthAddForm(
                    $idSpace,
                    $idUser,
                    $bkAuthAddForm->getParameter("resource"), /* stands for category id */
                    $bkAuthAddForm->getParameter("visa_id"),
                    $bkAuthAddForm->getParameter("date"),
                    $todo
                );
                if ($todo) {
                    return $this->redirect("spaceadminedit/" . $idSpace, ["showTodo" => true]);
                }
                $origin['page'] = 'bookingaccess';
            }
            $bkAuth = $bookingAuthCTRL->generateBkAuthTable($idSpace, $idUser, "corespaceuseredit", $lang);
            $bkAuthTableHtml = $bkAuth['bkTableHtml'];
            $bkHistoryTableHtml = $bookingAuthCTRL->generateHistoryTable($idSpace, $idUser, null, true);
            $forms['booking'] = ['forms' => [$bkAuthAddFormHtml, $bkAuthTableHtml, $bkHistoryTableHtml], 'show' => 0];
            $btnNames['booking'] = BookingTranslator::bookingauthorisations($lang);
        }

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($idSpace);
        $dataView = [
            'id_space' => $idSpace,
            'id_user' => $idUser,
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

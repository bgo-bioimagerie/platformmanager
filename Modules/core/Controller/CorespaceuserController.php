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

    
    // TODO: filter settings with activated modules => OK
    // TODO: design interface => OK
    // TODO: remove corespaceaccessusers user's buttons => OK
    // TODO: Show all history (not only for one resource) => OK
    // TODO: display flash messages ?
    // TODO: deal with pending accounts validation process => OK
    // TODO: make it clean (remove debug logs and non necessay comments)
    // TODO: manage buttons language => OK
    // TODO: avoid dynamicscript to load 2 times => OK
    // TODO: place clients before booking => depends on options order !!! => env line 75
    // TODO: test on space with not all modules activated
    // TODO: mention issue closing in last commit


    // space access section
    public function editAction($id_space, $id_user) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $origin = ["page" => json_encode($this->request->getParameterNoException("origin"))];

        $modelOptions = new CoreSpaceAccessOptions();
        $options = $modelOptions->getAll($id_space);
        $modules = array_map(function($option) { return $option['module'];}, $options);

        $spaceAccessForm = $this->generateSpaceAccessForm($id_space, $id_user);
        $spaceAccessFormHtml = $spaceAccessForm->getHtml($lang);
        if ($spaceAccessForm->check()) {
            $this->validateSpaceAccessForm($id_space, $id_user, $spaceAccessForm);
            $origin['page'] = 'spaceaccess';
        }

        $clientsUserFormHtml = "";
        $clientsUsertableHtml = "";
        if (in_array('clients', $modules)) {
            $clientsUsersCTRL = new ClientsuseraccountsController($this->request);
            $clientsUserForm = $clientsUsersCTRL->generateClientsUserForm($id_space, $id_user);
            $clientsUserFormHtml = $clientsUserForm->getHtml($lang);
            if ($clientsUserForm->check()) {
                $clientsUsersCTRL->validateClientsUserform($id_space, $id_user, $clientsUserForm);
                $origin['page'] = 'clientsuser';
            }
            $clientsUsertableHtml = $clientsUsersCTRL->generateClientsUserTable($id_space, $id_user);
        }

        $bkAuthAddFormHtml = "";
        $bkAuthTableHtml = "";
        $bkHistoryFormHtml = "";
        $bkHistoryTableHtml = "";
        if (in_array('booking', $modules)) {
            $bookingAuthCTRL = new BookingauthorisationsController($this->request);
            $bkAuthAddForm = $bookingAuthCTRL->generateBkAuthAddForm($id_space, $id_user, "corespaceuseredit");
            $bkAuthAddFormHtml = $bkAuthAddForm->getHtml($lang);
            $bkHistoryFormHtml = isset($bkHistoryForm) ? $bkHistoryForm->getHtml($lang) : "no booking history";
            if ($bkAuthAddForm->check()) {
                $bookingAuthCTRL->validateBkAuthAddForm($id_space, $id_user, $bkAuthAddForm, "corespaceuseredit");
                $origin['page'] = 'bookingaccess';
            }
            $bkAuthTableHtml = $bookingAuthCTRL->generateBkAuthTable($id_space, $id_user, "corespaceuseredit")['bkTableHtml'];
            $bkHistoryTableHtml = $bookingAuthCTRL->generateHistoryTable($id_space, $id_user, null, true);
        }
        
        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);
        $dataView = [
            'id_space' => $id_space,
            'id_user' => $id_user,
            'lang' => json_encode($lang),
            "space" => $space,
            'origin' => json_encode($origin),
            'options' => json_encode($options),
            "forms" => json_encode([
                'spaceaccess' => $spaceAccessFormHtml,
                'clientsuseraccounts' => $clientsUserFormHtml,
                'clientsuseraccountsTable' => $clientsUsertableHtml,
                'bookingauthorisations' => $bkAuthAddFormHtml,
                'bookingauthorisationsTable' => $bkAuthTableHtml,
                "bookinghistory" => $bkHistoryFormHtml,
                "bookinghistoryTable" => $bkHistoryTableHtml
            ])
        ];
        return $this->render($dataView, "editAction");
    }
}

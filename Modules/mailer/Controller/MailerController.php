<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/mailer/Model/MailerTranslator.php';
require_once 'Modules/mailer/Model/MailerSend.php';

require_once 'Modules/booking/Model/BkCalendarEntry.php';

require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';

require_once 'Modules/ecosystem/Model/EcUser.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class MailerController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("mailer");
        $_SESSION["openedNav"] = "mailer";
    }

    public function navbar($id_space) {
        return "";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("mailer", $id_space, $_SESSION["id_user"]);

        // get sort action
        $areasList = array();
        $resourcesList = array();

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "resources");
        if ($statusUserMenu > 0) {
            $modelArea = new ReArea();
            $areasList = $modelArea->getUnrestrictedAreasIDNameForSite($id_space);

            $modelResource = new ResourceInfo();
            $resourcesList = array();
            foreach ($areasList as $area) {
                $resourcesList[] = $modelResource->resourceIDNameForArea($area["id"]);
            }
        }

        $modelUser = new EcUser();
        $user = $modelUser->userAllInfo($_SESSION["id_user"]);
        $from = $user["email"];

        $lang = $this->getLanguage();
        $this->render(array("id_space" => $id_space, "lang" => $lang,
            'areasList' => $areasList,
            'resourcesList' => $resourcesList,
            'from' => $from));
    }

    public function sendAction($id_space) {
        $from = $this->request->getParameter("from");
        $to = $this->request->getParameter("to");
        $subject = $this->request->getParameter("subject");
        $content = $this->request->getParameter("content");

        // get the emails
        $toAdress = array();
        if ($to == "all") {
            $modelUser = new EcUser();
            $toAdress = $modelUser->getAllActifEmails();
        } elseif ($to == "managers") {
            $modelUser = new EcUser();
            $toAdress = $modelUser->getActiveManagersEmails($id_space);
        } else {
            $toEx = explode("_", $to);
            if ($toEx[0] == "a") { // area
                // get all the adresses of users who book in this area
                $modelCalEntry = new BkCalendarEntry();
                $toAdress = $modelCalEntry->getEmailsBookerArea($toEx[1]);
            } elseif ($toEx[0] == "r") { // resource
                // get all the adresses of users who book in this resource
                $modelCalEntry = new BkCalendarEntry();
                $toAdress = $modelCalEntry->getEmailsBookerResource($toEx[1]);
            }
        }
        
        // get the space name
        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        // send the email
        $mailerModel = new MailerSend();
        $message = $mailerModel->sendEmail($from, $space["name"], $toAdress, $subject, $content);

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'message' => $message
        ));
    }

}

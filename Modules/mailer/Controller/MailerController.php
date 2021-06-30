<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Email.php';
require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/mailer/Model/MailerTranslator.php';
require_once 'Modules/mailer/Model/MailerSend.php';

require_once 'Modules/booking/Model/BkCalendarEntry.php';

require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';

require_once 'Modules/core/Model/CoreUser.php';

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

        $modelUser = new CoreUser();
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

        if (!in_array($to, array("all", "managers"))) {
            $toEx = explode("_", $to);
            if ($toEx[0] == "a") { // area
                // get all the adresses of users who book in this area
                $modelCalEntry = new BkCalendarEntry();
                $to = $modelCalEntry->getEmailsBookerArea($toEx[1]);
            } elseif ($toEx[0] == "r") { // resource
                // get all the adresses of users who book in this resource
                $modelCalEntry = new BkCalendarEntry();
                $to = $modelCalEntry->getEmailsBookerResource($toEx[1]);
            }
        }

        $email = new Email();
        $mailParams = [
            "id_space" => $id_space,
            "content" => $content,
            "subject" => $subject,
            "from" => $from,
            "to" => $to,
        ];
        $message = $email->sendEmailToSpaceMembers($mailParams, $this->getLanguage());

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'message' => $message
        ));
    }

}

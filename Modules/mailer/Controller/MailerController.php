<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Email.php';
require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/mailer/Model/MailerTranslator.php';
require_once 'Modules/mailer/Model/MailerSend.php';
require_once 'Modules/mailer/Model/Mailer.php';

require_once 'Modules/booking/Model/BkCalendarEntry.php';

require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';

require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class MailerController extends CoresecureController
{
    public function navbar($id_space)
    {
        return "";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space)
    {
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
                $resourcesList[] = $modelResource->resourceIDNameForArea($id_space, $area["id"]);
            }
        }

        $modelUser = new CoreUser();
        $user = $modelUser->userAllInfo($_SESSION["id_user"]);
        $from = $user["email"];

        $mails = [];
        $mm = new Mailer();

        $modelConfig = new CoreConfig();
        $editRole = $modelConfig->getParamSpace("mailerEdit", $id_space, CoreSpace::$ADMIN);

        if ($this->role >= $editRole) {
            $mails = $mm->getMails($id_space);
        } elseif ($this->role >= CoreSpace::$USER) {
            $mails = $mm->getMails($id_space, Mailer::$SPACE_MEMBERS);
        }

        $userAppStatus = $modelUser->getStatus($_SESSION['id_user'] ?? 0);
        $superAdmin = false;
        if ($userAppStatus > CoreStatus::$USER) {
            $superAdmin = true;
        }

        $lang = $this->getLanguage();
        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            'areasList' => $areasList,
            'resourcesList' => $resourcesList,
            'from' => $from,
            'mails' => $mails,
            'superAdmin' => $superAdmin,
            'role' => $this->role,
            'editRole' => $editRole
        ));
    }

    public function deleteAction($id_space, $id)
    {
        $this->checkAuthorizationMenuSpace("mailer", $id_space, $_SESSION["id_user"]);
        $modelConfig = new CoreConfig();
        $editRole = $modelConfig->getParamSpace("mailerEdit", $id_space, CoreSpace::$ADMIN);
        if ($this->role < $editRole) {
            throw new PfmAuthException('not enough privileges');
        }
        $mm = new Mailer();
        $mm->delete($id_space, $id);
        $this->redirect('mailer/'.$id_space);
    }

    public function sendAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("mailer", $id_space, $_SESSION["id_user"]);

        $modelConfig = new CoreConfig();
        $editRole = $modelConfig->getParamSpace("mailerEdit", $id_space, CoreSpace::$ADMIN);

        if ($this->role < $editRole) {
            throw new PfmAuthException('not enough privileges');
        }
        $from = $this->request->getParameter("from");
        $to = $this->request->getParameter("to");
        $subject = $this->request->getParameter("subject");
        $content = $this->request->getParameter("content");

        $mailType = Mailer::$SPACE_MEMBERS;
        if (!in_array($to, array("all", "managers", "admins"))) {
            $toEx = explode("_", $to);
            if ($toEx[0] == "a") { // area
                // get all the adresses of users who book in this area
                $modelCalEntry = new BkCalendarEntry();
                $to = $modelCalEntry->getEmailsBookerArea($id_space, $toEx[1]);
            } elseif ($toEx[0] == "r") { // resource
                // get all the adresses of users who book in this resource
                $modelCalEntry = new BkCalendarEntry();
                $to = $modelCalEntry->getEmailsBookerResource($id_space, $toEx[1]);
            }
        } else {
            if ($to === "managers") {
                $mailType = Mailer::$SPACE_MANAGERS;
                $modelUser = new CoreUser();
                $content = "From " . $modelUser->getUserFullName($_SESSION["id_user"]) . " :</br>" . $content;
            } elseif ($to === "admins") {
                $mailType = Mailer::$SPACES_ADMINS;
                $modelUser = new CoreUser();
                $userAppStatus = $modelUser->getStatus($_SESSION['id_user'] ?? 0);
                if ($userAppStatus < CoreStatus::$ADMIN) {
                    throw new PfmAuthException('limited to super admins!!');
                }
                // get all space admins emails
                $modelSpaceUser = new CoreSpaceUser();
                $admins = $modelSpaceUser->admins();
                $emails = [];
                foreach ($admins as $admin) {
                    if ($admin['email']) {
                        $emails[] = ['email' => $admin['email']];
                    }
                }
                if (empty($emails)) {
                    throw new PfmParamException('no space admins found');
                }
                $to = $emails;
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
        $message = $email->sendEmailToSpaceMembers($mailParams, $this->getLanguage(), mailing: "mailer@$id_space");

        $mm = new Mailer();
        $mm->create($id_space, $subject, $content, $mailType);

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'message' => $message,
            'role' => $this->role
        ));
    }

    public function notifsAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("mailer", $id_space, $_SESSION["id_user"]);
        if (!$this->role || $this->role < CoreSpace::$USER) {
            return $this->render(['data' => ['notifs' => 0]]);
        }
        $mm = new Mailer();
        $recent = 0;
        if ($this->role == CoreSpace::$USER) {
            $recents = $mm->recent($id_space, Mailer::$SPACE_MEMBERS);
            if ($recents) {
                $recent = $recents['total'];
            }
        } elseif ($this->role == Corespace::$MANAGER) {
            $recents = $mm->recent($id_space, Mailer::$SPACE_MANAGERS);
            if ($recents) {
                $recent = $recents['total'];
            }
        } elseif ($this->role == Corespace::$ADMIN) {
            $recents = $mm->recent($id_space, Mailer::$SPACES_ADMINS);
            if ($recents) {
                $recent = $recents['total'];
            }
        }
        return $this->render(['data' => ['notifs' => $recent]]);
    }
}

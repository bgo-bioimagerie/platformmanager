<?php

require_once 'Framework/Model.php';
require_once 'Modules/mailer/Model/MailerTranslator.php';
require_once 'Modules/core/Model/CoreFiles.php';

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class defining methods to send an email
 *
 */
class Email extends Model {

    /**
     * 
     * Use configuration parameters to send an email
     * 
     * @param string $from email address from which the email is sent
     * @param string $fromName name of the sender displayed in mail header
     * @param string || array $toAddress the recipients email or array of email addresses. If an array, the first one is set as main recipient, the others as BCC
     * @param string $subject
     * @param string $content
     * @param string $sentCopyToFrom
     * @param array  list of CoreFiles to attach to email
     * @param bool   set toAddress as Bcc:, defaults to true, else just set in To:
     */
    public function sendEmail($from, $fromName, $toAddress, $subject, $content, $sentCopyToFrom = false, $files = [], $bcc=true ) {        
        // send the email
        $mail = new PHPMailer();
        $mail->IsHTML(true);
        $mail->isSMTP();
        $mail->Host = Configuration::get('smtp_host');
        $mail->Port = Configuration::get('smtp_port');
        $mail->CharSet = "utf-8";
        $mail->SetFrom($from, $fromName);
        $mail->Subject = $subject;
        $mail->addCustomHeader("X-PFM", "1");

        // parse content
        $content = preg_replace("/\r\n|\r/", "<br />", $content);
        $content = trim($content);
        $mail->Body = $content;

        if ($sentCopyToFrom){
            $mail->AddCC($from);
        }

        if (is_array($toAddress)){
            foreach($toAddress as $address){
                if($bcc) {            
                    $mail->addBCC($address);
                } else {
                    $mail->addAddress($address);
                }
            }
        } else if ( $toAddress != "" ) {
            if($bcc) {
                $mail->addBCC($toAddress);
            } else {
                $mail->addAddress($toAddress);
            }
        }

        $fm = new CoreFiles();
        foreach ($files as $file) {
            try {
                    $filePath = $fm->path($file);
                    $mail->AddAttachment($filePath, basename($file['name']));
            } catch(Exception $e) {
                Configuration::getLogger()->error('[mail] failed to attach file', ['file' => $file]);
            }
        }

        // get the language
        $lang = "En";
        if (isset ( $_SESSION ["user_settings"] ["language"] )) {
            $lang = $_SESSION ["user_settings"] ["language"];
        }

        if(!$mail->Send()) {
            return MailerTranslator::Message_Not_Send($lang) . $mail->ErrorInfo;
        } else {
            return MailerTranslator::Message_Send($lang);
        }
    }

    private function getFromEmail($spaceShortName) {
        $from = Configuration::get('smtp_from');
        $helpdeskEmail = Configuration::get('helpdesk_email');
        if($helpdeskEmail) {
            $helpdeskInfo = explode('@', $helpdeskEmail);
            $from = $helpdeskInfo[0].'+'.$spaceShortName.'@'.$helpdeskInfo[1];
        }
        return $from;
    }

    /**
     * 
     * Send an Email to all users or all managers within a space
     *
     * @param array $params
     *          required to fill sendEmail() parameters. Depends on why we want to notify space admins
     * @param string $lang
     * 
     * @return string result of call to function sendMail() telling if mail was sent or not
     */
    public function sendEmailToSpaceMembers($params, $lang = "") {
        $modelSpace = new CoreSpace();
        $spaceId = $params["id_space"];
        //$from = Configuration::get('smtp_from');
        $space = $modelSpace->getSpace($spaceId);
        $spaceName = $space['name'];
        // $spaceName = $modelSpace->getSpaceName($spaceId);
        // If helpdesk is activated
        if($modelSpace->getSpaceMenusRole($spaceId, "helpdesk")) {
            $from = $this->getFromEmail($space['shortname']);
        }
        $subject = $params["subject"];
        $fromName = "Platform-Manager";
        $subject = CoreTranslator::MailSubjectPrefix($spaceName, $lang) . " " . $subject;
        $mailerSetCopyToFrom = $this->getMailerSetCopyToFrom($spaceId);

        // get the emails
        switch ($params["to"]) {
            case "all":
                $toAddress =
                    $this->formatAddresses($modelSpace->getEmailsSpaceActiveUsers($spaceId));
                break;
            case "managers":
                $toAddress =
                    $this->formatAddresses($modelSpace->getEmailsSpaceManagers($spaceId));
                break;
            default:
                try {
                    $toAddress = $this->formatAddresses($params["to"]);
                } catch (Exception $e) {
                    Configuration::getLogger()->error('something went wrong getting email addresses', ['error' => $e->getMessage()]);
                    return "something went wrong!";
                }
                break;
                
        }
        return $this->sendEmail(
            $from,
            $fromName,
            $toAddress,
            $subject,
            $params["content"],
            $mailerSetCopyToFrom
        );
    }

    /**
     * 
     * Send an Email to space managers (status > 2) notifying that logged user requested to join space
     *
     * @param array
     *          $params required to fill sendEmail() parameters. Depends on why we want to notify space admins
     * @param string $origin determines how to get sendEmail() paramters from $params
     * @param string $lang
     */
    public function notifyAdminsByEmail($params, $origin, $lang = "") {
        $mailingCases = ["new_join_request", "self_registration"];
        if (in_array($origin, $mailingCases)) {
            $modelSpace = new CoreSpace();
            $from = Configuration::get('smtp_from');
            $fromName = "Platform-Manager";
            switch($origin) {
                case "new_join_request":
                    $spaceName = $params["space_name"] ?? '';
                    $userLogin = $_SESSION['login'];
                    $userEmail = $params['user_email'];
                    $idSpace = $params["id_space"];
                    break;
                case "self_registration":
                    $idSpace = $params['supData']['id_space'];
                    $spaceName = $modelSpace->getSpaceName($idSpace);
                    $userLogin = $params['supData']['login'];
                    $userEmail = $params['email'];
                    $organization = $params['supData']['organization'];
                    $team = $params['supData']['team'];
                    break;
                default:
                    break;
            }
            $subject = CoreTranslator::JoinRequestSubject($spaceName, $lang);
            $content = CoreTranslator::JoinRequestEmail($userLogin, $spaceName, $userEmail, $lang, $organization ?? '', $team ?? '');
            $toAddress = $this->formatAddresses($modelSpace->getEmailsSpaceManagers($idSpace));
            $this->sendEmail($from, $fromName, $toAddress, $subject, $content, false);
        } else {
            Configuration::getLogger()->error(
                "notifyAdminsByEmail",
                ["message" => "origin parameter is not set properly", "origin" => $origin]
            );
        }
    }

    /**
     * 
     * Send an Email to user. Manage the following cases :
     * - user account has been created
     * - user is accepted as a member of space
     * - user request to join a space is rejected
     *
     * @param array
     *          $params required to fill sendEmail() parameters. Depends on why we want to notify user
     * @param string $origin determines how to get sendEmail() paramters from $params
     * @param string $lang
     */
    public function notifyUserByEmail($params, $origin, $lang = "") {
        $fromName = "Platform-Manager";
        $from = Configuration::get('smtp_from');
        $spaceName = isset($params["space_name"]) ? $params["space_name"] : "";
        if(isset($params['id_space'])) {
            $modelSpace = new CoreSpace();
            if($modelSpace->getSpaceMenusRole($params['id_space'], "helpdesk")) {
                $space = $modelSpace->getSpace($params['id_space']);
                $from = $this->getFromEmail($space['shortname']);
                $spaceName = $space['name'];
            }
        }
        
        if ($origin === "add_new_user") {
            $fromName = "Platform-Manager";
            $toAddress = $params["email"];
            $subject = CoreTranslator::AccountCreatedSubject($spaceName, $lang);
            $content = CoreTranslator::AccountCreatedEmail($lang, $params["login"], $params["pwd"]);
        } else if ($origin === "accept_pending_user" || $origin === "reject_pending_user") {
            $accepted = ($origin === "accept_pending_user") ? true : false;
            $userModel = new CoreUser();
            $pendingUser = $userModel->getInfo($params["id_user"]);
            $userFullName = $pendingUser["firstname"] . " " . $pendingUser["name"];
            $subject = CoreTranslator::JoinResponseSubject($spaceName, $lang);
            $content = CoreTranslator::JoinResponseEmail($userFullName, $spaceName, $accepted, $lang);
            $toAddress = $pendingUser["email"];
        } else if ($origin == "add_new_user_waiting") {
            $fromName = "Platform-Manager";
            $toAddress = $params["email"];
            $subject = CoreTranslator::AccountPendingCreationSubject($lang);
            $content = CoreTranslator::AccountPendingCreationEmail($lang, $params["jwt"], $params["url"]);            
        } else {
            Configuration::getLogger()->error(
                "notifyUserByEmail",
                ["message" => "origin parameter is not set properly", "origin" => $origin]
            );
        }
        $this->sendEmail($from, $fromName, $toAddress, $subject, $content, false);
    }

    /**
     * 
     * Transforms array of objects with an "email" attribute into a list of email strings
     * 
     * @param $recipients
     * @return array of strings (emails)
     */
    public function formatAddresses($arrayOfObjectsWithEmailAttr) {
        $result = array();
        foreach ($arrayOfObjectsWithEmailAttr as $objectWithEmailAttr) {
            array_push($result, $objectWithEmailAttr["email"]);
        }
        return $result;
    }

    public function getMailerSetCopyToFrom($spaceId) {
        $modelConfig = new CoreConfig();
        $mailerSetCopyToFrom = $modelConfig->getParamSpace("MailerSetCopyToFrom", $spaceId);
        return ($mailerSetCopyToFrom == 1);
    }

}

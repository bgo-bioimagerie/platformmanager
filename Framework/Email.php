<?php

require_once 'Framework/Model.php';
require_once 'Modules/mailer/Model/MailerTranslator.php';
require_once 'Modules/core/Model/CoreFiles.php';
require_once 'Modules/core/Model/CoreMail.php';
require_once 'Modules/core/Model/CoreUser.php';


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
     * @param array custom headers to add to mail (["Auto-Submitted" => "auto-replied"])
     */
    public function sendEmail($from, $fromName, $toAddress, $subject, $content, $sentCopyToFrom = false, $files = [], $bcc=true, $mailing=null, $customHeaders=[]) {        
        // send the email
        $mail = new PHPMailer();
        $mail->IsHTML(true);
        $mail->isSMTP();
        $mail->Host = Configuration::get('smtp_host');
        $mail->Port = Configuration::get('smtp_port');
        if(Configuration::get('smtp_tls', false)) {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        }
        $mail->CharSet = "utf-8";
        $mail->SetFrom($from, $fromName);
        $mail->Subject = $subject;
        $mail->addCustomHeader("X-PFM", "1");
        foreach ($customHeaders as $key => $value) {
            $mail->addCustomHeader($key, $value);
        }

        // parse content
        $content = preg_replace("/\r\n|\r/", "<br />", $content);
        $content = trim($content);
        if($mailing) {
            // should diff between auto mails notif and manager sending mail to list
            $mailingInfo = explode("@", $mailing);
            $url = Configuration::get('PFM_PUBLIC_URL')."/coremail/$mailingInfo[1]";
            $mail->Body = $content . "<br/><small>You are registered to the pfm $mailingInfo[0] mailing list. To unsubscribe: <a href=\"$url\">$url</a></small>";
        } else {
            $mail->Body = $content;
        }

        if ($sentCopyToFrom){
            $mail->AddCC($from);
        }

        // filter email if user unsubscribed
        $cm = new CoreMail();
        $cu = new CoreUser();
        if (is_array($toAddress)){
            foreach($toAddress as $address){
                if($mailing) {
                    $mailingInfo = explode("@", $mailing);
                    $user = $cu->getUserByEmail($address);
                    if(!$user) {
                        Configuration::getLogger()->debug('[mail] user not found', ["mail" => $mailing, "user" => $address]);
                    }
                    if ($user && $cm->unsubscribed($user["id"], $mailingInfo[1], $mailingInfo[0])) {
                        Configuration::getLogger()->debug('[mail] user unsubscribed', ["mail" => $mailing, "user" => $address]);
                        continue;
                    }
                }
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
                if(is_string($file)){
                    $mail->AddAttachment($file, basename($file));
                } else {
                    $filePath = $fm->path($file);
                    $mail->AddAttachment($filePath, basename($file['name']));
                }
            } catch(Exception $e) {
                Configuration::getLogger()->error('[mail] failed to attach file', ['file' => $file]);
            }
        }

        // get the language
        $lang = "En";
        if (isset ( $_SESSION ["user_settings"] ["language"] )) {
            $lang = $_SESSION ["user_settings"] ["language"];
        }

        try {
            if(!$mail->Send()) {
                return MailerTranslator::Message_Not_Send($lang) . $mail->ErrorInfo;
            } else {
                return MailerTranslator::Message_Send($lang);
            }
        } catch(Exception $e){
            Configuration::getLogger()->error('[mail] failed to send email', ['error' => $mail->ErrorInfo, 'exception' => $e->getMessage()]);
            return MailerTranslator::Message_Not_Send($lang) . $mail->ErrorInfo;
        }
    }

    public function getFromEmail($spaceShortName) {
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
     * @param string $mailing  module name for mailing lists (will check that user did not unsubscribed)
     * 
     * @return string result of call to function sendMail() telling if mail was sent or not
     */
    public function sendEmailToSpaceMembers($params, $lang = "", $mailing=null) {
        $modelSpace = new CoreSpace();
        $spaceId = $params["id_space"];
        $space = $modelSpace->getSpace($spaceId);
        $spaceName = $space['name'];
        // If helpdesk is activated
        $from = Configuration::get('smtp_from');
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
                $toAddress = $this->formatAddresses($modelSpace->getEmailsSpaceActiveUsers($spaceId));
                break;
            case "managers":
                $toAddress = $this->formatAddresses($modelSpace->getEmailsSpaceManagers($spaceId));
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
            $mailerSetCopyToFrom,
            mailing: $mailing
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
                    $userLogin = $_SESSION['login'];       
                    $idSpace = $params["id_space"];
                    break;
                case "self_registration":
                    $idSpace = $params['supData']['id_space'];
                    $userLogin = $params['login'];
                    $organization = $params['supData']['organization'];
                    $unit = $params['supData']['unit'];
                    break;
                default:
                    break;
            }
            $spaceName = $modelSpace->getSpaceName($idSpace) ?? "";
            $userEmail = $params['email'] ?? "";
            $userFullName = $params['fullName'] ?? "";

            $subject = CoreTranslator::JoinRequestSubject($spaceName, $lang);
            $content = CoreTranslator::JoinRequestEmail($userLogin, $spaceName, $userEmail, $userFullName, $lang, $organization ?? '', $unit ?? '', $params['comment'] ?? '');
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
        $mailerSetCopyToFrom = $modelConfig->getParamSpace("MailerSetCopyToFrom", $spaceId, 1);
        return ($mailerSetCopyToFrom == 1);
    }

}

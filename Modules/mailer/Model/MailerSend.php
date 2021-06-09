<?php

require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/mailer/Model/MailerTranslator.php';
//require("externals/PHPMailer/PHPMailerAutoload.php");
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Class defining methods to send an email
 *
 * @author Sylvain Prigent
 */
class MailerSend extends Model {

    public function sendEmailSimulate($from, $fromName, $toAdress, $subject, $content, $sentCopyToFrom = true){
            echo "send email from " .$fromName. "(" . $from . ") to ";
            print_r($toAdress);
            echo " subject = " . $subject . " content = " . $content. "<br/>";
            if($sentCopyToFrom){
                echo " use copy to from <br/>";
            }
        }

    public function sendEmail($from, $fromName, $toAdress, $subject, $content, $sentCopyToFrom = false ){

        // send the email
        $mail = new PHPMailer();
        $mail->IsHTML(true);
        $smtp_host = Configuration::get('smtp_host');
        $smtp_port = Configuration::get('smtp_port');
        if (!empty($smtp_host)) {
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->Port = $smtp_port;
        }
        $mail->CharSet = "utf-8";
        $mail->SetFrom($from, $fromName);
        $mail->Subject = $subject;

        // parse content
        $content = preg_replace("/\r\n|\r/", "<br />", $content);
        $content = trim($content);

        $mail->Body = $content;

        if ($sentCopyToFrom){
            $mail->AddCC($from);
        }

        if (is_array ($toAdress)){
            foreach($toAdress as $addres){
                if ($addres[0] && $addres[0] != ""){
                                    //echo $addres[0] . "<br/>";
                                    //$mail->AddAddress($addres[0]);
                                    $mail->addBCC($addres[0]);
                }
            }
        }
        else{
            if ( $toAdress != "" ){
                //$mail->AddAddress($toAdress);
                $mail->addBCC($toAdress);
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

}

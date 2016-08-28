<?php

require_once 'Framework/Model.php';
require_once 'Modules/mailer/Model/MailerTranslator.php';
require("externals/PHPMailer/class.phpmailer.php");

/**
 * Class defining methods to send an email
 *
 * @author Sylvain Prigent
 */
class MailerSend extends Model {

	
	
	public function sendEmail($from, $fromName, $toAdress, $subject, $content, $sentCopyToFrom = true ){
		
		// send the email
		$mail = new PHPMailer();
		$mail->IsHTML(true);
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


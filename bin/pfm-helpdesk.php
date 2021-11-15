<?php
require_once __DIR__ . '/../vendor/autoload.php';

require_once 'Framework/Email.php';
require_once 'Modules/helpdesk/Model/Helpdesk.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreFiles.php';
require_once 'Framework/Events.php';

use League\HTMLToMarkdown\HtmlConverter;

function ignore($from, $subject) {
    if ($from->mailbox == "MAILER-DAEMON") {
        return true;
    }
    if (str_contains($subject, 'Delivery Status Notification')) {
        return true;
    }
    return false;
}

function _get_body_attach($mbox, $mid) {
    $struct = imap_fetchstructure($mbox, $mid);

    $parts = $struct->parts;
    $i = 0;
    if (!$parts) { /* Simple message, only 1 piece */
        $attachment = array(); /* No attachments */
        $content = imap_body($mbox, $mid);
    } else { /* Complicated message, multiple parts */
        $endwhile = false;
        $stack = array(); /* Stack while parsing message */
        $content = "";    /* Content of message */
        $attachment = array(); /* Attachments */

        while (!$endwhile) {
            if (!array_key_exists($i, $parts) || !$parts[$i]) {
                if (!empty($stack)) {
                    $parts = $stack[count($stack)-1]["p"];
                    $i     = $stack[count($stack)-1]["i"] + 1;
                    array_pop($stack);
                } else {
                    $endwhile = true;
                }
            }

            if (!$endwhile) {
                /* Create message part first (example '1.2.3') */
                $partstring = "";
                foreach ($stack as $s) {
                    $partstring .= ($s["i"]+1) . ".";
                }
                $partstring .= ($i+1);

                if (array_key_exists($i, $parts)) {
                    if (strtoupper($parts[$i]->disposition) == "ATTACHMENT" || strtoupper($parts[$i]->disposition) == "INLINE") { /* Attachment or inline images */
                        $filedata = imap_fetchbody($mbox, $mid, $partstring);
                        if ( $filedata != '' ) {
                            // handles base64 encoding or plain text
                            $decoded_data = base64_decode($filedata);
                            $name = "";
                            foreach($parts[$i]->parameters as $partParam) {
                                if($partParam->attribute == 'NAME') {
                                    $name = $partParam->value;
                                    break;
                                }
                            }
                            Configuration::getLogger()->debug('attach', ['data' => $parts[$i]->parameters]);
                            if ( !$decoded_data ) {
                                $attachment[] = array("filename" => $name,
                                    "filedata" => $filedata);
                            } else {
                                $attachment[] = array("filename" => $name,
                                    "filedata" => $decoded_data);
                            }
                        }
                    } elseif (strtoupper($parts[$i]->subtype) == "PLAIN" && strtoupper($parts[$i+1]->subtype) != "HTML") { /* plain text message */
                        $content .= imap_fetchbody($mbox, $mid, $partstring);
                    } elseif ( strtoupper($parts[$i]->subtype) == "HTML" ) {
                        /* HTML message takes priority */
                        $content .= imap_fetchbody($mbox, $mid, $partstring);
                    }
                }
            }

            if (array_key_exists($i, $parts) && $parts[$i]->parts) {
                if ( $parts[$i]->subtype != 'RELATED' ) {
                    // a glitch: embedded email message have one additional stack in the structure with subtype 'RELATED', but this stack is not present when using imap_fetchbody() to fetch parts.
                    $stack[] = array("p" => $parts, "i" => $i);
                }
                $parts = $parts[$i]->parts;
                $i = 0;
            } else {
                $i++;
            }
        } /* while */
    } /* complicated message */

    $ret = array();
    $ret['body'] = quoted_printable_decode($content);
    $ret['attachment'] = $attachment;
    return $ret;
}



$inbox = Configuration::get('helpdesk_imap_server');
$port = intval(Configuration::get('helpdesk_imap_port', 110));
$login = Configuration::get('helpdesk_imap_user');
$password = Configuration::get('helpdesk_imap_password');
$tls = Configuration::get('helpdesk_imap_tls');  //   '/ssl'
$origin = Configuration::get('helpdesk_email');
$originInfo = explode('@', $origin);
$originDomain = $originInfo[1];


if(!$inbox) {
    exit(0);
}

if(!$origin) {
    Configuration::getLogger()->error('No helpdesk_email defined');
    exit(1);
}

Configuration::getLogger()->debug('Connecting...', ['url' => $inbox.':'.$port.'/pop3'.$tls, 'login' => $login, 'tls' => $tls]);

while(true) {
    try {
    $mbox = imap_open('{'.$inbox.':'.$port.'/pop3'.$tls.'}', $login, $password);
    } catch(Throwable $err) {
        Configuration::getLogger()->error('Error', ['err' => $err]);
        exit(1);
    }
    $mails = FALSE;
    if (FALSE === $mbox) {
        Configuration::getLogger()->error('Connexion failed, check parameters!');
        exit(1);
    } else {
        $info = imap_check($mbox);
        if (FALSE !== $info) {
            $nbMessages = min(50, $info->Nmsgs);
            $mails = imap_fetch_overview($mbox, '1:'.$nbMessages, 0);
        } else {
            $err = 'Cannot read inbox';
        }
    }

    if (FALSE === $mails) {
        Configuration::getLogger()->error('Error', ['err' => $err]);
    } else {
        try {
            Configuration::getLogger()->debug('inbox', ['msgs' => $info->Nmsgs, 'recent' => $info->Recent]);

            $sp = new CoreSpace();
            $spaces = $sp->getSpaces('id');
            if(!$spaces) {
                sleep(Configuration::get('helpdesk_imap_sleep_seconds', 15 * 60)); // Wait 15 minutes or config defined
                continue;
            }
            $spaceNames = array();
            $impactedSpaces = array();
            foreach($spaces as $space) {
                $spaceNames[$space['shortname']] = $space['id'];
            }

            foreach ($mails as $mail) {
                $headerText = imap_fetchHeader($mbox, $mail->uid, FT_UID);
                $header = imap_rfc822_parse_headers($headerText);

                $mailContent = _get_body_attach($mbox, $mail->uid);
                imap_delete($mbox, $mail->uid);

                $from=$header->from;
                $to = $header->to;

                $id_space = 0;
                $toSpace = null;
                $otherDests = [];
                $spaceName = null;
                foreach($to as $dest) {
                    if($dest->host == $originDomain) {
                        $recipient = explode('+', $dest->mailbox);
                        if (count($recipient) == 1 || $recipient[1] == '') {
                            continue;
                        }
                        $spaceName = $recipient[1];
                        if($spaceName == 'donotreply') {
                            Configuration::getLogger()->debug("[helpdesk] reply to a donotreply!", ['dest' => $dest->host, 'from' => $from]);
                            continue;
                        }
                        if(!isset($spaceNames[$spaceName])) {
                            $otherDests[] = $dest->mailbox."@".$dest->host;
                            continue;
                        }
                        $id_space = $spaceNames[$spaceName];
                        $toSpace = $dest->mailbox."@".$dest->host;
                    } else {
                        $otherDests[] = $dest->mailbox."@".$dest->host;
                    }
                }
                if($id_space == 0) {
                    Configuration::getLogger()->info("Message not related to a space", ["to" => $to, "from" => $from, "subject" => $mail->subject]);
                    continue;
                }

                $spaceNames[$id_space] = true;


                Configuration::getLogger()->debug("New ticket", ["from" => $from[0]->personal." [".$from[0]->mailbox."@".$from[0]->host."]"]);
                $um = new CoreUser();
                $userEmail = $from[0]->mailbox."@".$from[0]->host;
                $user = $um->getUserByEmail($userEmail);
                $id_user = 0;
                if($user != null) {
                    $id_user = $user['id'];
                }
                $hm = new Helpdesk();
                $converter = new HtmlConverter(array('strip_tags' => true));
                $md = $converter->convert($mailContent['body']);
                $otherDestList = implode(',', $otherDests);
                $newTicket = $hm->createTicket($id_space, $userEmail, $otherDestList, $mail->subject, $md, $id_user);
                Configuration::getLogger()->debug('new ticket', ['ticket' => $newTicket]);
                $id_ticket = $newTicket['ticket'];
                $id_message = $newTicket['message'];
                foreach ($mailContent['attachment'] as $attachment) {
                    $c = new CoreFiles();
                    $role = CoreSpace::$MANAGER;
                    $module = "helpdesk";
                    $name = $attachment['filename'];
                    $attachId = $c->set(0, $id_space, $name, $role, $module, $id_user);
                    $file = $c->get($attachId);
                    $c->copyData($file, $attachment['filedata']);
                    $attachIds = $hm->attach($id_ticket, $id_message, [['id' => $attachId, 'name' => $name]]);
                    Configuration::getLogger()->debug('Attachements', ['ids' => $attachIds]);
                }
                if($newTicket['is_new']) {
                    if(ignore($from[0], $mail->subject)) {
                        Configuration::getLogger()->debug('[helpdesk] auto reply email, skip response');
                        continue;
                    }
                    Events::send(["action" => Events::HELPDESK_TICKET, "space" => ["id" => intval($id_space)]]);
                    $from = Configuration::get('helpdesk_email');
                    $fromInfo = explode('@', $from);
                    $from = $fromInfo[0]. '+' . $spaceName . '@' . $fromInfo[1];
                    $fromName = $fromInfo[0]. '+' . $spaceName;
                    $subject = '[Ticket #' . $id_ticket . '] '.$mail->subject;
                    $content = 'A new ticket has been created for '.$spaceName.' and will be managed soon.';
                    $e = new Email();
                    $e->sendEmail($from, $fromName, $userEmail, $subject, $content);
                }
                $hm->notify($id_space, $id_ticket, "en", $newTicket['is_new']);
            }
            imap_close($mbox, CL_EXPUNGE);

            $hm = new Helpdesk();
            $hm->remind();
        } catch(Throwable $e) {
            Configuration::getLogger()->error('[helpdesk] something went wrong', ['error' => $e->getMessage(), 'line' => $e->getLine(), "file" => $e->getFile(),  'stack' => $e->getTraceAsString()]);
        }

    }
    sleep(Configuration::get('helpdesk_imap_sleep_seconds', 15 * 60)); // Wait 15 minutes or config defined

    }
?>

<?php
require_once 'Framework/Configuration.php';
require_once 'Framework/Controller.php';
require_once 'Framework/Errors.php';
require_once 'Framework/Email.php';
require_once 'Framework/Events.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/helpdesk/Model/Helpdesk.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreFiles.php';
require_once 'Modules/core/Model/CoreUserSpaceSettings.php';
require_once 'Modules/core/Controller/CorespaceController.php';


use League\CommonMark\CommonMarkConverter;

class HelpdeskController extends CoresecureController {
    
    public function mainMenu() {
        $id_space = isset($this->args['id_space']) ? $this->args['id_space'] : null;
        if ($id_space) {
            $csc = new CoreSpaceController($this->request);
            return $csc->navbar($id_space);
        }
        return null;
    }

    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("helpdesk", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $spaceModel = new CoreSpace();
        $role = $spaceModel->getUserSpaceRole($id_space, $_SESSION['id_user']);
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("helpdesk", $id_space);
        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "role" => $role,
            "ticket" => null,
            "menuInfo" => $menuInfo
        ));
    }

    public function setSettingsAction($id_space) {
        $this->checkAuthorizationMenuSpace("helpdesk", $id_space, $_SESSION["id_user"]);
        $role = $this->spaceModel->getUserSpaceRole($id_space, $_SESSION['id_user']);
        if ($role < CoreSpace::$MANAGER) {
            throw new PfmAuthException('not authorized', 403);
        }
        $lang = $this->getLanguage();

        $cussm = new CoreUserSpaceSettings();
        $settings = $this->request->getParameter('settings');
        $cussm->setUserSettings($id_space, $_SESSION["id_user"], "hp_notifyNew", $settings['notifyNew'] ? 1 : 0);
        $cussm->setUserSettings($id_space, $_SESSION["id_user"], "hp_notifyAssignedUpdate", $settings['notifyAssignedUpdate'] ? 1 : 0);
        $cussm->setUserSettings($id_space, $_SESSION["id_user"], "hp_notifyAllUpdate", $settings['notifyAllUpdate'] ? 1 : 0);
        $this->render(array("id_space" => $id_space, "lang" => $lang, "data" => [
            "settings" => $settings
        ]));
    }

    public function settingsAction($id_space) {
        $this->checkAuthorizationMenuSpace("helpdesk", $id_space, $_SESSION["id_user"]);


        $cussm = new CoreUserSpaceSettings();
        $settings = $cussm->getUserSettings($id_space, $_SESSION["id_user"]);

        $this->render(array("data" => [
            "settings" => [
                'notifyNew' => ($settings["hp_notifyNew"] ?? 0) ? true : false,
                'notifyAssignedUpdate' => ($settings["hp_notifyAssignedUpdate"] ?? 0 ) ? true : false,
                'notifyAllUpdate' => ($settings["hp_notifyAllUpdate"] ?? 0) ? true : false
            ]
        ]));

    }

    /**
     * reply or add note
     */
    public function assignAction($id_space, $id_ticket) {
        $this->checkAuthorizationMenuSpace("helpdesk", $id_space, $_SESSION["id_user"]);
        $sm = new CoreSpace();
        $role = $sm->getUserSpaceRole($id_space, $_SESSION['id_user']);
        if(!$role || $role < CoreSpace::$MANAGER) {
            throw new PfmAuthException('not authorized', 403);
        }
        $hm = new Helpdesk();
        $ticket = $hm->get($id_ticket);
        if(!$ticket) {
            throw new PfmException('ticket not found', 404);
        }
        if($ticket['id_space'] != $id_space) {
            throw new PfmAuthException('not authorized', 403);
        }
        $hm->assign($id_ticket, $_SESSION['id_user']);
        $this->render(['data' => ['ticket' => $ticket]]);
    }

    /**
     * Update ticket info/status/...
     */
    public function statusAction($id_space, $id_ticket, $status) {
        $this->checkAuthorizationMenuSpace("helpdesk", $id_space, $_SESSION["id_user"]);
        $sm = new CoreSpace();
        $role = $sm->getUserSpaceRole($id_space, $_SESSION['id_user']);
        if(!$role || $role < CoreSpace::$MANAGER) {
            throw new PfmAuthException('not authorized', 403);
        }
        $hm = new Helpdesk();
        $ticket = $hm->get($id_ticket);
        if(!$ticket) {
            throw new PfmException('ticket not found', 404);
        }
        if($ticket['id_space'] != $id_space) {
            throw new PfmAuthException('not authorized', 403);
        }
        $hm->setStatus($id_ticket, $status);
        $this->render(['data' => ['ticket' => $ticket]]);
    }

    /**
     * reply or add note
     */
    public function messageAction($id_space, $id_ticket) {
        $this->checkAuthorizationMenuSpace("helpdesk", $id_space, $_SESSION["id_user"]);
        $hm = new Helpdesk();
        $ticket = $hm->get($id_ticket);
        if(!$ticket && $id_ticket > 0) {
            throw new PfmException('ticket not found', 404);
        }
        if($id_ticket > 0 && $ticket['id_space'] != $id_space) {
            throw new PfmAuthException('not authorized', 403);
        }

        $sm = new CoreSpace();
        $role = $sm->getUserSpaceRole($id_space, $_SESSION['id_user']);
        if(!$role) {
            throw new PfmAuthException('not authorized', 403);
        }

        if($role < CoreSpace::$MANAGER && $id_ticket == 0) {
            // user not manager not creator of ticket
            throw new PfmAuthException('not authorized', 403);
        }

        if($role < CoreSpace::$MANAGER && $ticket['created_by_user'] != $_SESSION['id_user']) {
            // user not manager not creator of ticket
            throw new PfmAuthException('not authorized', 403);
        }
        $space = $sm->getSpace($id_space);
        $params = $this->request->params();
        $id = 0;
        $isNew = false;
        if(intval($params['type']) == Helpdesk::$TYPE_EMAIL) {
            Configuration::getLogger()->debug('[helpdesk] mail reply', ['params' => $params, 'files' => $_FILES]);
            $attachments = [];
            $attachementFiles = [];
            foreach($_FILES as $fid => $f) {
                $c = new CoreFiles();
                $role = CoreSpace::$MANAGER;
                $module = "helpdesk";
                $name = $_FILES[$fid]['name'];
                $fileNameOK = preg_match("/^[0-9a-zA-Z\-_\.]+$/", $name, $matches);
                if(! $fileNameOK) {
                    throw new PfmFileException("invalid file name, must be alphanumeric:  [0-9a-zA-Z\-_\.]+", 403);
                }
                $attachId = $c->set(0, $id_space, $name, $role, $module, $_SESSION['id_user']);
                $file = $c->get($attachId);
                $attachementFiles[] = $file;
                $filePath = $c->path($file);
                if(!move_uploaded_file($_FILES[$fid]["tmp_name"], $filePath)) {
                    Configuration::getLogger()->error('[helpdesk] file upload error', ['file' => $_FILES[$fid], 'to' => $filePath]);
                    throw new PfmFileException("Error, there was an error uploading your file", 500);
                }
                $attachments[] = ['id' => $attachId, 'name' => $name];
            }

            $from = $hm->fromAddress($space);
            $fromName = 'pfm-' . $space['shortname'];
            
            $toAddress = explode(',', $params['to']);

            if($id_ticket == 0) {
                $isNew = true;
                $newTicket = $hm->createTicket($id_space, $toAddress[0], $from, $params['subject'], $params['body'], 0, $attachments);
                $id_ticket = $newTicket['ticket'];
                $ticket = $hm->get($id_ticket);
                $id = $newTicket['message'];
            } else {
                $id = $hm->addEmail($id_ticket, $params['body'], $_SESSION['email'], $attachments);
            }

            $subject = '[Ticket #' . $ticket['id'] . '] '.$ticket['subject'];

            $converter = new CommonMarkConverter([
                'html_input' => 'strip',
                'allow_unsafe_links' => false,
            ]);
            
            $content = $converter->convertToHtml($params['body']);
            Configuration::getLogger()->debug('send email', ['body' => $content, 'files' => $attachementFiles]);
            
            
            $e = new Email();
            $e->sendEmail($from, $fromName, $toAddress, $subject, $content, false, $attachementFiles, false);

        } else {
            $id = $hm->addNote($id_ticket, $params['body'], $_SESSION['email']);
        }
        $hm->notify($id_space, $id_ticket, "en", $isNew);

        Events::send(["action" => Events::ACTION_HELPDESK_TICKET, "space" => ["id" => intval($id_space)]]);

        
        $this->render(['data' => ['message' => ['id' => $id], 'ticket' => ['id' => $ticket['id']]]]);

    }

    /**
     * Get messages
     */
    public function messagesAction($id_space, $id_ticket) {
        $this->checkAuthorizationMenuSpace("helpdesk", $id_space, $_SESSION["id_user"]);
        $hm = new Helpdesk();
        $ticket = $hm->get($id_ticket);
        if(!$ticket) {
            throw new PfmAuthException('ticket not found', 404);
        }
        $ticket['status'] = intval($ticket['status']);
        if($ticket['id_space'] != $id_space) {
            throw new PfmAuthException('not in space', 403);
        }
        $messages = $hm->getMessages($id_ticket);
        $filter = false;
        $sm = new CoreSpace();
        $um = new CoreUser();

        if($um->getStatus($_SESSION['id_user']) != CoreUser::$ADMIN) {
            $role = $sm->getUserSpaceRole($id_space, $_SESSION['id_user']);
            if(!$role || $role < CoreSpace::$MANAGER) {
                $filter = true;
            }
        }
        $filteredMessages = [];
        if($filter) {
            // Not manager, remove notes and reject if not creator
            foreach ($messages as $message) {
                if($ticket['created_by_user'] != $_SESSION['id_user']) {
                    throw new PfmAuthException('not ticket owner', 403);
                }
                if(intval($message['type']) == HelpDesk::$TYPE_EMAIL) {
                    $filteredMessages[] = $message;
                }
            }

        } else {
            $filteredMessages = $messages;
            $hm->markRead($id_ticket);
        }

        $attachements = $hm->getAttachments($id_ticket);
        $attachementsPerMessage = [];
        foreach ($attachements as $attachement) {
            if(!isset($attachementsPerMessage[$attachement['id_message']])) {
                $attachementsPerMessage[$attachement['id_message']] = array();
            }
            $attachementsPerMessage[$attachement['id_message']][] = $attachement;
        }
        foreach ($filteredMessages as $index => $msg) {
            $filteredMessages[$index]['attachements'] = [];
            if(isset($attachementsPerMessage[$msg['id']])) {
                $filteredMessages[$index]['attachements'] = $attachementsPerMessage[$msg['id']];
            }
        }

        $this->render(['data' => ['ticket' => $ticket, 'messages' => $filteredMessages]]);
    }

    /**
     * Count unread messages per status (managers only)
     */
    public function unreadCountAction($id_space) {
        $this->checkAuthorizationMenuSpace("helpdesk", $id_space, $_SESSION["id_user"]);
        $sm = new CoreSpace();
        $role = $sm->getUserSpaceRole($id_space, $_SESSION['id_user']);
        if(!$role || $role < CoreSpace::$MANAGER) {
            $this->render(['data' => ['unread' => []]]);
            return;
        }
        $hm = new Helpdesk();
        $tickets = $hm->unread($id_space);
        $this->render(['data' => ['unread' => $tickets]]);
    }

    /**
     * List tickets in desired status
     * 
     * If not admin of space, returns only tickets assigned or opened by session user
     * If GET request parameter contains *mine* then returns only tickets created or assigned by current user
     */
    public function listAction($id_space, $status) {
        $this->checkAuthorizationMenuSpace("helpdesk", $id_space, $_SESSION["id_user"]);

        $hm = new Helpdesk();
        $id_user = 0;
        $sm = new CoreSpace();
        $role = $sm->getUserSpaceRole($id_space, $_SESSION['id_user']);
        if(!$role || $role < CoreSpace::$MANAGER) {
            $id_user = $_SESSION['id_user'];
        }
        
        if(!$id_user && isset($_GET['mine'])) {
            $id_user = $_SESSION['id_user'];
        }

        $offset = 0;
        $limit = 50;

        if(isset($_GET['offset'])) {
            $offset = intval($_GET['offset']);
        }

        if(isset($_GET['limit'])) {
            $limit = intval($_GET['limit']);
        }

        $tickets = $hm->list($id_space, $status, $id_user, $offset, $limit);

        $this->render(['data' => ['tickets' => $tickets, 'offset' => $offset, 'limit' => $limit]]);
    }

}

?>

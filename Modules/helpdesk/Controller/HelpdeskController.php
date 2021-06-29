<?php
require_once 'Framework/Configuration.php';
require_once 'Framework/Controller.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/helpdesk/Model/Helpdesk.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreUser.php';


class HelpdeskController extends CoresecureController {
    
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("helpdesk", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $this->render(array("id_space" => $id_space, "lang" => $lang));
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
        if(!$ticket) {
            throw new PfmException('ticket not found', 404);
        }
        if($ticket['id_space'] != $id_space) {
            throw new PfmAuthException('not authorized', 403);
        }

        $sm = new CoreSpace();
        $role = $sm->getUserSpaceRole($id_space, $_SESSION['id_user']);
        if(!$role) {
            throw new PfmAuthException('not authorized', 403);
        }
        if($role < CoreSpace::$MANAGER && $ticket['created_by_user'] != $_SESSION['id_user']) {
            // user not manager not creator of ticket
            throw new PfmAuthException('not authorized', 403);
        }
        $params = $this->request->params();
        $id = 0;
        if($params['type'] == Helpdesk::$TYPE_EMAIL) {
            // TODO manage attachements
            $id = $hm->addEmail($id_ticket, $params['body'], $_SESSION['email']);
            // TODO send email to users (param *to*, comma separated)
            // need to convert body to html
        } else {
            $id = $hm->addNote($id_ticket, $params['body'], $_SESSION['email']);

        }
        
        $this->render(['data' => ['message' => ['id' => $id]]]);

    }

    /**
     * Get messages
     */
    public function messagesAction($id_space, $id_ticket) {
        $this->checkAuthorizationMenuSpace("helpdesk", $id_space, $_SESSION["id_user"]);
        $hm = new Helpdesk();
        $ticket = $hm->get($id_ticket);
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
            // Not manager, remove notes
            foreach ($messages as $message) {
                if(intval($message['type']) == HelpDesk::$TYPE_EMAIL) {
                    $filteredMessages[] = $message;
                }
            }

        } else {
            $filteredMessages = $messages;
        }


        $this->render(['data' => ['ticket' => $ticket, 'messages' => $filteredMessages]]);
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

        $tickets = $hm->list($id_space, $status, $id_user);

        //$this->render(["data" => ["test" => 123, "other" => $this->request->params()]]);
        $this->render(['data' => ['tickets' => $tickets]]);
    }

}

?>

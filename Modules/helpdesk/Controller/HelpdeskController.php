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
        // $lang = $this->getLanguage();

        $this->render(array("id_space" => $id_space, "lang" => $lang));
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
        $um = new CoreUser();
        if($um->getStatus($_SESSION['id_user']) != CoreUser::$ADMIN) {
            $role = $sm->getUserSpaceRole($id_space, $_SESSION['id_user']);
            if(!$role || $role < CoreSpace::$MANAGER) {
                $id_user = $_SESSION['id_user'];
            }
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

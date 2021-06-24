<?php
require_once 'Framework/Configuration.php';
require_once 'Framework/Controller.php';

require_once 'Modules/core/Controller/CoresecureController.php';

class HelpdeskController extends CoresecureController {
    
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("helpdesk", $id_space, $_SESSION["id_user"]);
        // $lang = $this->getLanguage();

        $this->render(array("id_space" => $id_space, "lang" => $lang));
    }

    public function listAction($id_space, $status) {
        //Configuration::getLogger()->debug('API test', ['params' => $this->request->params()]);
        $this->checkAuthorizationMenuSpace("helpdesk", $id_space, $_SESSION["id_user"]);

        // Get user space status
        // if user status >= 4 list all 
        // Else get user customer ones (created_by)

        $this->render(["data" => ["test" => 123, "other" => $this->request->params()]]);
    }

}

?>

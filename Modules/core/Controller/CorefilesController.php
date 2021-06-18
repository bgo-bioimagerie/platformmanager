<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreFiles.php';
require_once 'Modules/core/Model/CoreSpace.php';

class CorefilesController extends CoresecureController {

    /**
     * (non-PHPdoc)
     * @see Controller::downloadAction()
     */
    public function downloadAction($id_space, $id_file) {
        $modelFiles = new CoreFiles();
        $f = $modelFiles->get($id_file);
        if($f == null) {
            throw new PfmFileException("No file found", 1);
        }
        if(intval($f['role'] != CoreSpace::$VISITOR)) { // anyone can access
            if(!isset($_SESSION['id_user'])) {  // not connected
                throw new PfmFileException("Not authorized", 1);
            }
            $modelSpace = new CoreSpace();
            $userRole = $modelSpace->getUserSpaceRole($id_space, $_SESSION['id_user']);
            if(intval($f['role']) > intval($userRole)) {
                throw new PfmFileException("Not authorized", 1);
            }
        }
        $modelFiles->download($f);        
    }

}
?>

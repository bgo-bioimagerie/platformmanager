<?php

require_once 'Framework/Model.php';

require_once 'Modules/helpdesk/Model/Helpdesk.php';


/**
 * Class defining methods to install and initialize the helpdesk database
 *
 * @author Olivier Sallou
 */
class HelpdeskInstall extends Model {

    /**
     * Create the Helpdesk database
     */
    public function createDatabase() {

        $model = new Helpdesk();
	$model->createTable();

    }
}
?>

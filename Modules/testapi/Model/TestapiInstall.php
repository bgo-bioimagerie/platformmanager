<?php

require_once 'Framework/Model.php';
require_once 'Modules/testapi/Model/TestapiPeople.php';

/**
 * Class defining methods to install and initialize the core database
 *
 * @author Sylvain Prigent
 */
class TestapiInstall extends Model {

    /**
     * Create the core database
     *
     * @return boolean True if the base is created successfully
     */
    public function createDatabase() {        

        $model = new TestapiPeople();
        $model->createTable();
        
        if (!file_exists('data/testapi/')) {
            mkdir('data/testapi/', 0777, true);
        }
    }
}

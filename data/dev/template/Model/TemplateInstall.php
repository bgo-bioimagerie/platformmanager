<?php

require_once 'Framework/Model.php';
require_once 'Modules/template/Model/Provider.php';

/**
 * Class defining methods to install and initialize the Template database
 *
 * @author Sylvain Prigent
 */
class TemplateInstall extends Model {

    /**
     * Create the Template database
     */
    public function createDatabase() {        

        // initialise the Provider table
        $model = new Provider(); 
        $model->createTable();
   
    }
}

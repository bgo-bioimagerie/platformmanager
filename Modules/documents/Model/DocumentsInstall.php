<?php

require_once 'Framework/Model.php';
require_once 'Modules/documents/Model/Document.php';

/**
 * Class defining methods to install and initialize the core database
 *
 * @author Sylvain Prigent
 */
class DocumentsInstall extends Model {

    /**
     * Create the core database
     *
     * @return boolean True if the base is created successfully
     */
    public function createDatabase() {        

        $model = new Document();
        $model->createTable();
        
        if (!file_exists('data/documents/')) {
            mkdir('data/documents/', 0755, true);
        }
    }
}

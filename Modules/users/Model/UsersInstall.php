<?php

require_once 'Framework/Model.php';
require_once 'Modules/users/Model/UsersInfo.php';
require_once 'Modules/users/Model/UsersPatch.php';

/**
 * Class defining methods to install and initialize the Users database
 *
 * @author Sylvain Prigent
 */
class UsersInstall extends Model {

    /**
     * Create the Users database
     */
    public function createDatabase() {        

        // initialise the Provider table
        $model1 = new UsersInfo(); 
        $model1->createTable();
   
        $model2 = new UsersPatch();
        $model2->patch();
        
        if (!file_exists('data/users/')) {
            mkdir('data/users/', 0777, true);
        }
        if (!file_exists('data/users/avatar/')) {
            mkdir('data/users/avatar/', 0777, true);
        }
        
    }
}

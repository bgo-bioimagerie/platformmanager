<?php

require_once 'Framework/Model.php';
require_once 'Modules/users/Model/UsersInfo.php';

/**
 * Class defining methods to install and initialize the Users database
 *
 * @author Sylvain Prigent
 */
class UsersInstall extends Model
{
    /**
     * Create the Users database
     */
    public function createDatabase()
    {
        // initialise the Provider table
        $model1 = new UsersInfo();
        $model1->createTable();

        if (!file_exists('data/users/')) {
            mkdir('data/users/', 0755, true);
        }
        if (!file_exists('data/users/avatar/')) {
            mkdir('data/users/avatar/', 0755, true);
        }
    }
}

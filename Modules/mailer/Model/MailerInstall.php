<?php

require_once 'Framework/Model.php';

/**
 * Class defining methods to install and initialize the core database
 *
 * @author Sylvain Prigent
 */
class MailerInstall extends Model {

    /**
     * Create the core database
     *
     * @return boolean True if the base is created successfully
     */
    public function createDatabase() {        

        if (!file_exists('data/mailer/')) {
            mkdir('data/mailer/', 0777, true);
        }
    }
}

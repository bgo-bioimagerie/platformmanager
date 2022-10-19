<?php

require_once 'Framework/Controller.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * Manage the units (each user belongs to an unit)
 *
 * @author sprigent
 *
 */
class DocumentsController extends CoresecureController
{
    public function navbar($id_space)
    {
        return file_get_contents('Modules/documents/View/Documents/navbar.php');
    }
}

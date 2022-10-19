<?php

require_once 'Framework/Model.php';

require_once 'Modules/catalog/Model/CaCategory.php';
require_once 'Modules/catalog/Model/CaEntry.php';

/**
 * Class defining methods to install and initialize the core database
 *
 * @author Sylvain Prigent
 */
class CatalogInstall extends Model
{
    /**
     * Create the core database
     *
     * @return boolean True if the base is created successfully
     */
    public function createDatabase()
    {
        $modulesModel1 = new CaCategory();
        $modulesModel1->createTable();

        $modulesModel2 = new CaEntry();
        $modulesModel2->createTable();

        if (!file_exists('data/catalog/logos')) {
            mkdir('data/catalog/logos', 0755, true);
        }
    }
}

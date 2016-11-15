<?php

require_once 'Framework/Model.php';
require_once 'Modules/database/Model/DbDatabase.php';
require_once 'Modules/database/Model/DbLang.php';
require_once 'Modules/database/Model/DbDatabaseTranslate.php';
require_once 'Modules/database/Model/DbClass.php';
require_once 'Modules/database/Model/DbClassTranslate.php';
require_once 'Modules/database/Model/DbType.php';
require_once 'Modules/database/Model/DbAttribut.php';
require_once 'Modules/database/Model/DbView.php';
require_once 'Modules/database/Model/DbViewAttribut.php';
require_once 'Modules/database/Model/DbViewTranslate.php';
require_once 'Modules/database/Model/DbMenu.php';
require_once 'Modules/database/Model/DbMenuTranslate.php';
require_once 'Modules/database/Model/DbAttributTranslate.php';

/**
 * Class defining methods to install and initialize the core database
 *
 * @author Sylvain Prigent
 */
class DatabaseInstall extends Model {

    /**
     * Create the core database
     *
     * @return boolean True if the base is created successfully
     */
    public function createDatabase() {        

        $model1 = new DbDatabase();
        $model1->createTable();
        
        $model2 = new DbLang();
        $model2->createTable();
        $model2->setDefault();
        
        $model3 = new DbDatabaseTranslate();
        $model3->createTable();
        
        $model4 = new DbClass();
        $model4->createTable();
        
        $model5 = new DbClassTranslate();
        $model5->createTable();
        
        $model6 = new DbType();
        $model6->createTable();
        $model6->setDefault();
        
        $model7 = new DbAttribut();
        $model7->createTable();
        
        $model8 = new DbView();
        $model8->createTable();
        
        $model9 = new DbViewAttribut();
        $model9->createTable();
        
        $model10 = new DbViewTranslate();
        $model10->createTable();
        
        $model11 = new DbMenu();
        $model11->createTable();
        
        $model12 = new DbMenuTranslate();
        $model12->createTable();
        
        $model13 = new DbAttributTranslate();
        $model13->createTable();
        
        if (!file_exists('./data/database/')) {
            mkdir('./data/database/', 0777, true);
        }
    }
}

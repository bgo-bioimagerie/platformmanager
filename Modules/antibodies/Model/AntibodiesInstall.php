<?php

require_once 'Framework/Model.php';
require_once 'Modules/antibodies/Model/Anticorps.php';
require_once 'Modules/antibodies/Model/Isotype.php';
require_once 'Modules/antibodies/Model/Source.php';
require_once 'Modules/antibodies/Model/Espece.php';
require_once 'Modules/antibodies/Model/Tissus.php';
require_once 'Modules/antibodies/Model/AcProtocol.php';
require_once 'Modules/antibodies/Model/Organe.php';
require_once 'Modules/antibodies/Model/Prelevement.php';
require_once 'Modules/antibodies/Model/Status.php';

require_once 'Modules/antibodies/Model/Dem.php';
require_once 'Modules/antibodies/Model/Aciinc.php';
require_once 'Modules/antibodies/Model/AcOwner.php';
require_once 'Modules/antibodies/Model/Linker.php';
require_once 'Modules/antibodies/Model/Inc.php';
require_once 'Modules/antibodies/Model/Acii.php';
require_once 'Modules/antibodies/Model/Kit.php';
require_once 'Modules/antibodies/Model/Proto.php';
require_once 'Modules/antibodies/Model/Fixative.php';
require_once 'Modules/antibodies/Model/AcOption.php';
require_once 'Modules/antibodies/Model/Enzyme.php';

require_once 'Modules/antibodies/Model/AcApplication.php';
require_once 'Modules/antibodies/Model/AcStaining.php';

/**
 * Class defining methods to install and initialize the core database
 *
 * @author Sylvain Prigent
 */
class AntibodiesInstall extends Model {

    /**
     * Create the anticorps database
     *
     * @return boolean True if the base is created successfully
     */
    public function createDatabase(){
        
        $anticorpsModel = new Anticorps();
        $anticorpsModel->createTable();
        
        $isotypeModel = new Isotype();
        $isotypeModel->createTable();
        
        $sourceModel = new Source();
        $sourceModel->createTable();
        
        $especeModel = new Espece();
        $especeModel->createTable();
        
        $tissusModel = new Tissus();
        $tissusModel->createTable();
        
        $protoModel = new AcProtocol();
        $protoModel->createTable();

        $modelAcOwner = new AcOwner();
        $modelAcOwner->createTable();
        
        $organeModel = new Organe();
        $organeModel->createTable();
        
        $organePrelevement = new Prelevement();
        $organePrelevement->createTable();
        
        $modelStatus = new Status();
        $modelStatus->createTable();
        
        $modelacii = new Acii();
        $modelacii->createTable();
        
        $modelaciinc = new Aciinc();
        $modelaciinc->createTable();
        
        $modelaciinc = new Dem();
        $modelaciinc->createTable();
        
        $modelinc = new Inc();
        $modelinc->createTable();
        
        $modellinker = new Linker();
        $modellinker->createTable();
        
        $model = new Kit();
        $model->createTable();
        
        $model = new Proto();
        $model->createTable();
        
        $model = new Fixative();
        $model->createTable();
        
        $model = new AcOption();
        $model->createTable();
        
        $model = new Enzyme();
        $model->createTable();
                
        $modelApp = new AcApplication();
        $modelApp->createTable();
                
        $modelStaining = new AcStaining();
        $modelStaining->createTable();

        $dir= "data/antibodies";
        if(!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
                
        return 'success';
    }
}


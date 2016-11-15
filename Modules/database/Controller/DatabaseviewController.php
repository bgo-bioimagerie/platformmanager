<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/database/Model/DatabaseTranslator.php';
require_once 'Modules/database/Model/DbMenu.php';
require_once 'Modules/database/Model/DbDatabase.php';
require_once 'Modules/database/Model/DbMenuTranslate.php';
require_once 'Modules/database/Model/DbViewAttribut.php';
require_once 'Modules/database/Model/DbClass.php';
require_once 'Modules/database/Model/DbQuery.php';
require_once 'Modules/database/Model/DbAttribut.php';
require_once 'Modules/database/Model/DbClassTranslate.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class DatabaseviewController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("database");
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $id_database) {
        $this->checkAuthorizationMenuSpace("database", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        
        $modelDb = new DbDatabase();
        $database = $modelDb->get($id_database);
        
        $menu = $this->buildMenu($id_space, $id_database, $lang);
        //print_r($menu);
        
        $this->render(array("id_space" => $id_space, "lang" => $lang, "menu" => $menu, "database" => $database));
    }
    
    protected function buildMenu($id_space, $id_database, $lang){
        $menuModel = new DbMenu();
        $menu = $menuModel->getForDatabase($id_database);
        
        $menuTrModel = new DbMenuTranslate();
        for($i = 0 ; $i < count($menu) ; $i++){
            $menu[$i]["print_name"] = $menuTrModel->getTranslation($menu[$i]["id"], $lang);
            
            if($menu[$i]["edit_or_view"] == "c"){
                $menu[$i]["url"] = "databaseviewform/".$id_space."/".$id_database."/".$menu[$i]["table_id"]; 
            }
            else{
                $menu[$i]["url"] = "databaseviewview/".$id_space."/".$id_database."/".$menu[$i]["table_id"]; 
            }
        }
        return $menu;
    }
    
    public function viewAction($id_space, $id_database, $id_table){
        $this->checkAuthorizationMenuSpace("database", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        $modelDb = new DbDatabase();
        $database = $modelDb->get($id_database);
        
        $table = new TableView();
        
        // get view attributs
        $modelViewAtt = new DbViewAttribut();
        $tableAttributs = $modelViewAtt->getForViewTable($id_table, $lang);
        
        $modelClass = new DbClass();
        $classInfo = $modelClass->get($id_table);
        
        $headers = array();
        foreach($tableAttributs as $tatt){
            $headers[$tatt["name"]] = $tatt["print_name"];
        }
        
        $modelQuery = new DbQuery();
        $data = $modelQuery->tableView($classInfo["name"]);
        
        $tableHtml = $table->view($data, $headers);
        
        $menu = $this->buildMenu($id_space, $id_database, $lang);
        $this->render( array("id_space" => $id_space, "database" => $database, 
                             "lang" => $lang, "tableHtml" => $tableHtml, "menu" => $menu) );
    }
    
    public function formAction($id_space, $id_database, $id_class){
        $lang = $this->getLanguage();
        
        // get the data
        $modelClass = new DbClass();
        $classInfo = $modelClass->get($id_class);
        
        $modelAtt = new DbAttribut();
        $atts = $modelAtt->getByClass($id_class);
        
        // make the form
        $form = new Form($this->request, "classform".$classInfo["name"]);
        $form->setTitle($modelClass->getPrintName($id_class, $lang));
        foreach($atts as $att){
            if($att["type"] == 1){
                $form->addNumber($name, $label, $isMandatory, $value);
            }
            else if($att["type"] == 2){
                
            }
            else if($att["type"] == 3){
                
            }
            else if($att["type"] == 4){
                
            }
            else if($att["type"] == 5){
                
            }
            
        }
        
        $modelDb = new DbDatabase();
        $database = $modelDb->get($id_database);
        $menu = $this->buildMenu($id_space, $id_database, $lang);
        $this->render( array("id_space" => $id_space, "database" => $database, 
                             "lang" => $lang, "formHtml" => $form->getHtml($lang), "menu" => $menu) );
    }
}

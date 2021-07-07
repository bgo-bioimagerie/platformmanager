<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';

//require_once 'Modules/core/Model/CoreTranslator.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreMenu.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CoremenusController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction() {

        $lang = $this->getLanguage();

        $modelMenu = new CoreMenu();
        $menus = $modelMenu->getMenus("display_order");
        $ids = array(); $names = array(); $displays = array();
        foreach($menus as $menu){
            $ids[] = $menu["id"];
            $names[] = $menu["name"];
            $displays[] = $menu["display_order"];
        }
        
        $form = new Form($this->request, "indexmenuAction");
        $form->setTitle(CoreTranslator::Menus($lang));
        
        $formAdd = new FormAdd($this->request, "indexmenuActionList");
        $formAdd->addHidden("id", $ids);
        $formAdd->addText("name", CoreTranslator::Name($lang), $names);
        $formAdd->addNumber("display_order", CoreTranslator::Display_order($lang), $displays);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form->setFormAdd($formAdd);  
        $form->setValidationButton(CoreTranslator::Save($lang), "coremenus");
        $form->setButtonsWidth(2, 9);
        
        if ($form->check()){
            $ids = $this->request->getParameterNoException("id");
            $names = $this->request->getParameterNoException("name");
            $displayOrder = $this->request->getParameterNoException("display_order");
            
            //print_r($packageID);


            $packs = [];
            for ($p = 0; $p < count($ids); $p++) {
                if ($names[$p] != "" && $ids[$p]) {
                   $packs[$names[$p]] = $ids[$p];
                }
            }
            for ($p = 0; $p < count($ids); $p++) {
                if (!$ids[$p]) {
                    // If package id not set, use from known packages
                    if(isset($packs[$names[$p]])) {
                        $ids[$p] = $packs[$names[$p]];
                    } else {
                        // Or create a new package
                       $cvm = new CoreVirtual();
                       $vid = $cvm->new('menu');
                       $ids[$p] = $vid;
                       $packs[$names[$p]] = $vid;
                   }
                }
                $modelMenu->setMenu($ids[$p], $names[$p], $displayOrder[$p]);
            }


            /* bug posible conflict on getting id            
            $count = 0;
            
            // get the last package id
            $lastID = 0;
            for( $p = 0 ; $p < count($ids) ; $p++){
                if ($names[$p] != "" ){
                    if ($ids[$p] > $lastID){
                        $lastID = $ids[$p];
                    }
                }
            }
                
            for( $p = 0 ; $p < count($ids) ; $p++){
                if ($names[$p] != "" ){
                    $curentID = $ids[$p];

                    if ($curentID == ""){
                        $lastID++;
                        $curentID = $lastID;
                        $ids[$p] = $lastID;
                    }
                    if ($curentID == 1 && $p > 0){
                        $lastID++;
                        $curentID = $lastID;
                        $packageID[$p] = $lastID;
                    }
                    //echo "set package (".$curentID." , " . $id_resource ." , " . $packageName[$p]." , ". $packageDuration[$p] . ")<br/>";
                    $modelMenu->setMenu($curentID, $names[$p], $displayOrder[$p]);
                    $count++;
                }
            }
            */
            $modelMenu->removeUnlistedMenus($ids);
            $_SESSION["message"] = CoreTranslator::Menus_saved($lang);
            $this->redirect("coremenus");
            return;
        }
        
        // view
        $formHtml = $form->getHtml($lang);
        return $this->render(array(
            'lang' => $lang,
            'formHtml' => $formHtml
                ));

    }
    
    /**
     * 
     */
    public function itemsAction(){
        
        $lang = $this->getLanguage();
        
        $table = new TableView();
        $table->setTitle(CoreTranslator::MenuItems($lang));
        
        $modelMenu = new CoreMenu();
        $data = $modelMenu->getItems(); 
        for($i = 0 ; $i < count($data) ; $i++){
            $data[$i]["menu"] = $modelMenu->menuName($data[$i]["id_menu"]);
        }
        
        $headers = array(
            "name" => CoreTranslator::Name($lang), 
            "menu" => CoreTranslator::Menu($lang),
            "display_order" => CoreTranslator::Display_order($lang)
            );
        
        $table->addLineEditButton("coremenusitemedit");
        $table->addDeleteButton("coremenusitemdelete");
        $tableHtml = $table->view($data, $headers);
        
        return $this->render(array("lang" => $lang, "tableHtml" => $tableHtml));
        
    }
    
    /**
     * 
     * @param type $id
     */
    public function itemeditAction($id){
        
        // queries
        $modelMenu = new CoreMenu();
        $item = $modelMenu->getItem($id);
        
        $menus = $modelMenu->getMenus("name");
        $choices = array(); $choicesid = array();
        foreach($menus as $menu){
            $choices[] = $menu["name"];
            $choicesid[] = $menu["id"];
        }
        
        // form
        $lang = $this->getLanguage();
        
        $form = new Form($this->request, "itemeditAction");
        $form->setTitle(CoreTranslator::Menus($lang));
        
        $form->addText("name", CoreTranslator::Name($lang), true, $item["name"]);
        $form->addText("url", CoreTranslator::Url($lang), true, $item["link"]);
        $form->addUpload("icon", CoreTranslator::Icon($lang));
        $form->addTextArea("description", CoreTranslator::Description($lang), false, $item["description"]);
        $form->addSelect("id_menu", CoreTranslator::Menu($lang), $choices, $choicesid, $item["id_menu"]);
        $form->addColor("color", CoreTranslator::color($lang), false, $item["color"]);
        $form->addNumber("display_order", CoreTranslator::Display_order($lang), false, $item["display_order"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "coremenusitemedit/".$id);
        $form->setButtonsWidth(2, 9);
        
        if ($form->check()){
            $name = $this->request->getParameterNoException("name");
            $url = $this->request->getParameterNoException("url");
            $id_menu = $this->request->getParameterNoException("id_menu");
            $color = $this->request->getParameterNoException("color");
            $display_order = $this->request->getParameterNoException("display_order");
            $description = $this->request->getParameterNoException("description");
            
            $id = $modelMenu->setDataMenu($id, $name, $url, $id_menu, $color, $display_order, $description);
            
            $target_dir = "data/core/menu/";
            if ($_FILES["icon"]["name"] != "") {
                $ext = pathinfo($_FILES["icon"]["name"], PATHINFO_EXTENSION);
                FileUpload::uploadFile($target_dir, "icon", $id . "." . $ext);
                $modelMenu->setDataMenuIcon($id, $target_dir . $id . "." . $ext);
            }
            
            $this->redirect("coremenusitems");
            return;
        }
        
        // view
        $formHtml = $form->getHtml($lang);
        return $this->render(array(
            'lang' => $lang,
            'formHtml' => $formHtml
                ));
    }
    
    /**
     * 
     * @param type $id
     */
    public function itemdeleteAction($id){
        $model = new CoreMenu();
        $model->removeDataMenu($id);
        
        $this->redirect("coremenusitems");
    }

}

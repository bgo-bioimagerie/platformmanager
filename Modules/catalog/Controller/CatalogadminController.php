<?php
require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/catalog/Model/CatalogTranslator.php';
require_once 'Modules/catalog/Model/CaCategory.php';
require_once 'Modules/catalog/Model/CaEntry.php';
require_once 'Modules/catalog/Controller/CatalogController.php';
/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CatalogadminController extends CatalogController {
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        //$this->checkAuthorizationMenu("catalog");

    }
    
    public function indexAction($id_space){
        $this->redirect("catalogcategories/".$id_space);
    }
    /**
     * (non-PHPdoc)
     * @see Controller::categoriesAction()
     */
    public function categoriesAction($id_space) {
        $this->checkAuthorizationMenuSpace("catalogsettings", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        // get the user list
        $modelCategory = new CaCategory();
        $categoriesArray = $modelCategory->getAll($id_space);
        $table = new TableView();
        $table->setTitle(CatalogTranslator::Categories($lang));
        $table->addLineEditButton("catalogcategoryedit/".$id_space);
        $table->addDeleteButton("catalogcategorydelete/".$id_space);
        $tableHtml = $table->view($categoriesArray, array("id" => "ID", "name" => CoreTranslator::Name($lang), "display_order" => CoreTranslator::Display_order($lang)));
        $this->render(array(
            'id_space' => $id_space,
            'tableHtml' => $tableHtml
        ));
    }
    public function categoryeditAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("catalogsettings", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        // get name
        $name = "";
        $display_order = 0;
        $modelCategory = new CaCategory();
        if ($id > 0) {
            $name = $modelCategory->getName($id_space, $id);
            $display_order = $modelCategory->getDisplayOrder($id_space ,$id);
        }
        // build the form
        $form = new Form($this->request, "formcategories");
        $form->setTitle(CatalogTranslator::Category($lang));
        $form->addHidden("id", $id);
        $form->addText("name", "name", true, $name);
        $form->addText("display_order", CoreTranslator::Display_order($lang), true, $display_order);
        $form->setValidationButton("Ok", "catalogcategoryedit/".$id_space ."/" . $id);
        //$form->setCancelButton(CoreTranslator::Cancel($lang), "catalogadmin/categories");
        if ($form->check()) {
            if ($id > 0) {
                $modelCategory->edit($form->getParameter("id"), $id_space, $form->getParameter("name"), $form->getParameter("display_order"));
            } else {
                $modelCategory->add($id_space, $form->getParameter("name"), $form->getParameter("display_order"));
            }
            $this->redirect("catalogcategories/".$id_space);
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // view
            $this->render(array(
                'id_space' => $id_space,
                'formHtml' => $formHtml
            ));
        }
    }
    /**
     * Remove a category from the database
     */
    public function categorydeleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("catalogsettings", $id_space, $_SESSION["id_user"]);
        $modelCategory = new CaCategory();
        $modelCategory->delete($id_space, $id);
        // generate view
        $this->redirect("catalogcategories/".$id_space);
    }
    public function prestationsAction($id_space) {
        $this->checkAuthorizationMenuSpace("catalogsettings", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        // get the user list
        $modelEntry = new CaEntry();
        $dataArray = $modelEntry->getAll($id_space);
        $modelCategory = new CaCategory();
        for ($i = 0; $i < count($dataArray); $i++) {
            $dataArray[$i]["id_category"] = $modelCategory->getName($id_space, $dataArray[$i]["id_category"]);
        }
        $table = new TableView();
        $table->setTitle(CatalogTranslator::Entries($lang));
        $table->addLineEditButton("catalogprestationedit/".$id_space);
        $table->addDeleteButton("catalogprestationdelete/".$id_space, "id", "title");
        $tableHtml = $table->view($dataArray, array("id" => "ID", "title" => CatalogTranslator::Title($lang),
            "id_category" => CatalogTranslator::Category($lang),
            "short_desc" => CatalogTranslator::Short_desc($lang)
        ));
        $this->render(array(
            'id_space' => $id_space,
            'tableHtml' => $tableHtml
        ));
    }
    public function prestationeditAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("catalogsettings", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        // get info
        $modelEntry = new CaEntry();
        $entryInfo = array("title" => "", "id_category" => 0, "short_desc" => "", "full_desc" => "");
        if ($id > 0) {
            $entryInfo = $modelEntry->getInfo($id_space, $id);
        }
        // categories choices
        $modelCategory = new CaCategory();
        $categories = $modelCategory->getAll($id_space);
        $cchoices = array();
        $cchoicesid = array();
        foreach ($categories as $cat) {
            $cchoices[] = $cat["name"];
            $cchoicesid[] = $cat["id"];
        }
        // build the form
        $form = new Form($this->request, "formcategories");
        $form->setTitle(CatalogTranslator::Entry($lang));
        $form->addHidden("id", $id);
        $form->addText("title", CatalogTranslator::Title($lang), true, $entryInfo["title"]);
        $form->addSelect("id_category", CatalogTranslator::Category($lang), $cchoices, $cchoicesid, $entryInfo["id_category"]);
        $form->addTextArea("short_desc", CatalogTranslator::Short_desc($lang), false, $entryInfo["short_desc"]);
        $form->addUpload("illustration", CatalogTranslator::Illustration($lang));
        $form->setValidationButton(CoreTranslator::Ok($lang), "catalogprestationedit/".$id_space . "/" . $id);
        //$form->setCancelButton(CoreTranslator::Cancel($lang), "catalogadmin/entries");
        if ($form->check()) {
            $id_category = $form->getParameter("id_category");
            $title = $form->getParameter("title");
            $short_desc = $form->getParameter("short_desc");
            $full_desc = ""; //$form->getParameter("full_desc");
            if ($id > 0) {
                $modelEntry->edit($id, $id_space, $id_category, $title, $short_desc, $full_desc);
            } else {
                $id = $modelEntry->add($id_space, $id_category, $title, $short_desc, $full_desc);
            }
            if ($_FILES["illustration"]["name"] != "") {
                // upload file
                $this->uploadIllustration();
                // set filename to database
                $modelEntry->setImageUrl($id_space, $id, $_FILES["illustration"]["name"]);
            }
            $this->redirect("catalogprestations/".$id_space);
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // view
            $this->render(array(
                'id_space' => $id_space,
                'formHtml' => $formHtml
            ));
        }
    }
    protected function uploadIllustration() {
        $target_dir = "data/catalog/";
        $target_file = $target_dir . $_FILES["illustration"]["name"];

        $fileNameOK = preg_match("/^[0-9a-zA-Z\-_\.]+$/", $_FILES["illustration"]["name"], $matches);
        if(! $fileNameOK) {
            throw new PfmFileException("invalid file name, must be alphanumeric:  [0-9a-zA-Z\-_\.]+", 403);
        }

        // Check file size
        if ($_FILES["illustration"]["size"] > 500000000) {
            return "Error: your file is too large.";
        }
        
        if (move_uploaded_file($_FILES["illustration"]["tmp_name"], $target_file)) {
            return "The file logo file" . basename($_FILES["illustration"]["name"]) . " has been uploaded.";
        } else {
            return "Error, there was an error uploading your file.";
        }
    }

    public function prestationdeleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("catalogsettings", $id_space, $_SESSION["id_user"]);
        $modelCategory = new CaEntry();
        $modelCategory->delete($id_space ,$id);
        // generate view
        $this->redirect("catalogprestations/".$id_space);
    }
}
<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/database/Model/DatabaseInstall.php';
require_once 'Modules/database/Model/DatabaseTranslator.php';
require_once 'Modules/database/Model/DbDatabase.php';
require_once 'Modules/database/Model/DbType.php';
require_once 'Modules/database/Model/DbAttribut.php';
require_once 'Modules/database/Model/DbView.php';
require_once 'Modules/database/Model/DbMenu.php';
require_once 'Modules/database/Model/DbInstaller.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class DatabaseconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new Exception("Error 503: Permission denied");
        }
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $databaseModel = new DbDatabase();
        $databases = $databaseModel->getBySpace($id_space, $lang);

        $this->render(array("id_space" => $id_space, "lang" => $lang, "databases" => $databases));
    }

    public function infoAction($id_space, $id) {

        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $databaseModel = new DbDatabase();
        $info = $databaseModel->get($id);
        $databases = $databaseModel->getBySpace($id_space, $lang);

        $langModels = new DbLang();
        $langs = $langModels->getAllForForm();

        $databaseModelTranslate = new DbDatabaseTranslate();
        $translations = $databaseModelTranslate->getAllForForm($id);

        $form = new Form($this->request, "databaseconfiginfoform");
        $form->setTitle(DatabaseTranslator::Database_informations($lang));
        $form->addText("name", CoreTranslator::Name($lang), true, $info["name"]);

        $subForm = new FormAdd($this->request, "databaseconfiginfosubform");
        $subForm->addSelect("langs", DatabaseTranslator::lang($lang), $langs["names"], $langs["ids"], $translations["ids"]);
        $subForm->addText("translations", CoreTranslator::name($lang), $translations["names"]);
        $subForm->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($subForm, DatabaseTranslator::View_name($lang));
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "databaseconfiginfo/" . $id_space . "/" . $id);

        if ($form->check()) {
            $id = $databaseModel->set($id, $id_space, $form->getParameter("name"));
            $databaseModelTranslate->setAll($id, $form->getParameter("langs"), $form->getParameter("translations"));

            $this->redirect("databaseconfiginfo/" . $id_space . "/" . $id);
            return;
        }

        if ($id == 0) {
            $menuCode = array(1, 1, 1, 1, 1, 1, 1);
        } else {
            $menuCode = array(1, 0, 0, 0, 0, 0, 0);
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang, "databases" => $databases,
            "form" => $form->getHtml($lang), "menuCode" => $menuCode, "id_database" => $id));
    }

    public function classesAction($id_space, $id_database, $id_class) {

        $lang = $this->getLanguage();

        $databaseModel = new DbDatabase();
        $databases = $databaseModel->getBySpace($id_space, $lang);

        // get the classesllist
        $modelDbClass = new DbClass();
        $classes = $modelDbClass->getForDatabase($id_database, $lang);

        $classModelTranslate = new DbClassTranslate();

        $formEdit = $this->createClassFormEdit($id_space, $id_database, $id_class, $lang);
        if ($formEdit->check()) {
            $id_class = $modelDbClass->set($id_class, $id_database, $formEdit->getParameter("name"));
            $classModelTranslate->setAll($id_class, $formEdit->getParameter("langs"), $formEdit->getParameter("translations"));

            $this->redirect("databaseconfigclasses/" . $id_space . "/" . $id_database . "/" . $id_class);
            return;
        }

        $formAttributs = $this->createAttributsFormEdit($id_space, $id_database, $id_class, $lang);
        if ($formAttributs->check()) {
            $modelAttribut = new DbAttribut();
            $id = $formAttributs->getParameter("id");
            $type = $formAttributs->getParameter("type");
            $name = $formAttributs->getParameter("name");
            $mandatory = $formAttributs->getParameter("mandatory");
            $foreign_class_id = $formAttributs->getParameter("foreign_class_id");
            $foreign_class_att = $formAttributs->getParameter("foreign_class_att");
            $modelAttribut->setAll($id, $id_class, $type, $name, $mandatory, $foreign_class_id, $foreign_class_att);

            $this->redirect("databaseconfigclasses/" . $id_space . "/" . $id_database . "/" . $id_class);
            return;
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang,
            "classes" => $classes, "menuCode" => array(0, 1, 0, 0, 0, 0, 0),
            "id_database" => $id_database,
            "databases" => $databases,
            "formEdit" => $formEdit->getHtml($lang), "formAttributs" => $formAttributs->getHtml($lang)));
    }

    protected function createClassFormEdit($id_space, $id_database, $id_class, $lang) {

        $modelClass = new DbClass();
        $info = $modelClass->get($id_class);

        $langModels = new DbLang();
        $langs = $langModels->getAllForForm();

        $databaseModelTranslate = new DbClassTranslate();
        $translations = $databaseModelTranslate->getAllForForm($id_class);

        $form = new Form($this->request, "editClassForm");
        $form->setTitle(DatabaseTranslator::Classe($lang) . ": " . $info["name"], 3);
        $form->addText("name", CoreTranslator::Name($lang), true, $info["name"]);

        $subForm = new FormAdd($this->request, "databaseconfigclasssubform");
        $subForm->addSelect("langs", DatabaseTranslator::lang($lang), $langs["names"], $langs["ids"], $translations["ids"]);
        $subForm->addText("translations", CoreTranslator::name($lang), $translations["names"]);
        $subForm->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($subForm, DatabaseTranslator::View_name($lang));
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "databaseconfigclasses/" . $id_space . "/" . $id_database . "/" . $id_class);

        return $form;
    }

    protected function createAttributsFormEdit($id_space, $id_database, $id_class, $lang) {
        $form = new Form($this->request, "editAttributsForm");
        $form->setTitle(DatabaseTranslator::Attributs($lang), 3);

        $dbTypesModel = new DbType();
        $types = $dbTypesModel->getAllForForm();

        $modelAtt = new DbAttribut();
        $atts = $modelAtt->getForClass($id_class);

        $subForm = new FormAdd($this->request, "attributsconfigclasssubform");
        $subForm->addHidden("id", $atts["id"]);
        $subForm->addText("name", CoreTranslator::Name($lang), $atts["name"]);
        $subForm->addSelect("type", DatabaseTranslator::Type($lang), $types["names"], $types["ids"], $atts["type"]);
        $subForm->addText("foreign_class_id", DatabaseTranslator::Foreign_class($lang), $atts["foreign_class_id"]);
        $subForm->addText("foreign_class_att", DatabaseTranslator::Foreign_key($lang), $atts["foreign_class_att"]);
        $subForm->addSelect("mandatory", DatabaseTranslator::Mandatory($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $atts["mandatory"]);
        $subForm->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($subForm, "");
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "databaseconfigclasses/" . $id_space . "/" . $id_database . "/" . $id_class);

        return $form;
    }

    public function viewsAction($id_space, $id_database, $id_view) {

        $lang = $this->getLanguage();

        $databaseModel = new DbDatabase();
        $databases = $databaseModel->getBySpace($id_space, $lang);

        // get the classesllist

        $modelDbView = new DbView();
        $views = $modelDbView->getForDatabase($id_database, $lang);

        $formEdit = $this->createViewFormEdit($id_space, $id_database, $id_view, $lang);
        if ($formEdit->check()) {
            $id_view = $modelDbView->set($id_view, $id_database, $formEdit->getParameter("name"), $formEdit->getParameter("id_class"));
            $this->redirect("databaseconfigviews/" . $id_space . "/" . $id_database . "/" . $id_view);
            return;
        }

        if ($id_view > 0) {
            $formAttributs = $this->createViewAttsFormEdit($id_space, $id_database, $id_view, $lang);
            if ($formAttributs->check()) {
                $modelAttribut = new DbViewAttribut();

                $id = $formAttributs->getParameter("id");
                $id_attribut = $formAttributs->getParameter("id_attribut");
                $display_order = $formAttributs->getParameter("display_order");
                $foreign_att_print = $formAttributs->getParameter("foreign_att_print");

                $modelAttribut->setAll($id, $id_view, $id_attribut, $display_order, $foreign_att_print);

                $this->redirect("databaseconfigviews/" . $id_space . "/" . $id_database . "/" . $id_view);
                return;
            }
            $formAttributsHtml = $formAttributs->getHtml($lang);
        } else {
            $formAttributsHtml = "";
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang,
            "views" => $views, "menuCode" => array(0, 0, 1, 0, 0, 0, 0),
            "id_database" => $id_database,
            "databases" => $databases,
            "formEdit" => $formEdit->getHtml($lang), "formAttributs" => $formAttributsHtml)
        );
    }

    public function createViewFormEdit($id_space, $id_database, $id_view, $lang) {

        $modelView = new DbView();
        $view = $modelView->get($id_view);

        $modelDbClass = new DbClass();
        $classes = $modelDbClass->getForDatabaseSelect($id_database, $lang);

        $form = new Form($this->request, "vieweditform");
        $form->setTitle(DatabaseTranslator::Views($lang));
        $form->addText("name", CoreTranslator::Name($lang), true, $view["name"]);
        $form->addSelect("id_class", DatabaseTranslator::Classe($lang), $classes["names"], $classes["ids"], $view["id_class"]);
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "databaseconfigviews/" . $id_space . "/" . $id_database . "/" . $id_view);

        return $form;
    }

    public function createViewAttsFormEdit($id_space, $id_database, $id_view, $lang) {

        $modelView = new DbView();
        $view = $modelView->get($id_view);

        $modelClassAtt = new DbAttribut();
        $classesAtt = $modelClassAtt->getForClassSelect($view["id_class"]);

        $modelViewAtt = new DbViewAttribut();
        $viewAtt = $modelViewAtt->getForView($id_view);

        $form = new Form($this->request, "editAttributsForm");
        $form->setTitle(DatabaseTranslator::Attributs($lang), 3);

        $formAdd = new FormAdd($this->request, "vieweditformAdd");
        $formAdd->addHidden("id", $viewAtt["id"]);
        $formAdd->addSelect("id_attribut", DatabaseTranslator::Classe($lang), $classesAtt["names"], $classesAtt["ids"], $viewAtt["id_attribut"]);
        $formAdd->addText("foreign_att_print", DatabaseTranslator::Foreign_key($lang), $viewAtt["foreign_att_print"]);
        $formAdd->addNumber("display_order", DatabaseTranslator::Display_order($lang), $viewAtt["display_order"]);

        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($formAdd, "");
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "databaseconfigviews/" . $id_space . "/" . $id_database . "/" . $id_view);

        return $form;
    }

    public function menuAction($id_space, $id_database) {

        $lang = $this->getLanguage();

        $modelLang = new DbLang();
        $laguage = $modelLang->getAll();

        $modelMenu = new DbMenu();
        $menu = $modelMenu->getForDatabaseSelect($id_database);

        $choices = array();
        $choicesid = array();
        $modelClasses = new DbClass();
        $classes = $modelClasses->getForDatabase($id_database, $lang);
        foreach ($classes as $c) {
            $choicesid[] = "c_" . $c["id"];
            $choices[] = $c["name"];
        }
        $modelView = new DbView();
        $views = $modelView->getForDatabase($id_database, $lang);
        foreach ($views as $v) {
            $choicesid[] = "v_" . $v["id"];
            $choices[] = $v["name"];
        }

        $modelMenuTr = new DbMenuTranslate();

        $form = new Form($this->request, "menuactionform");
        //$form->setTitle(DatabaseTranslator::Menu($lang), 3);

        $formAdd = new FormAdd($this->request, "menuactionformadd");
        $formAdd->addHidden("id", $menu["id"]);
        $formAdd->addSelect("class_view", DatabaseTranslator::Page($lang), $choices, $choicesid, $menu["class_view"]);
        $formAdd->addNumber("display_order", DatabaseTranslator::Display_order($lang), $menu["display_order"]);
        foreach ($laguage as $langu) {
            $trs = $modelMenuTr->getTranslations($menu["id"], $langu["short"]);
            $formAdd->addText($langu["short"], DatabaseTranslator::Text($lang) . " " . $langu["name"], $trs);
        }
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($formAdd, "");
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "databaseconfigmenu/" . $id_space . "/" . $id_database . "/");

        if ($form->check()) {
            // items
            $id = $form->getParameter("id");
            $class_view = $form->getParameter("class_view");
            $display_order = $form->getParameter("display_order");

            $ids = $modelMenu->setAll($id, $id_database, $class_view, $display_order);

            foreach ($laguage as $langu) {
                //echo "set lang = " . $langu["short"] . "<br/>";
                $modelMenuTr->setAll($ids, $langu["short"], $form->getParameter($langu["short"]));
            }

            $this->redirect("databaseconfigmenu/" . $id_space . "/" . $id_database . "/");
            return;
        }

        $databaseModel = new DbDatabase();
        $databases = $databaseModel->getBySpace($id_space, $lang);
        $this->render(array("id_space" => $id_space, "id_database" => $id_database, "lang" => $lang, "menuCode" => array(0, 0, 0, 1, 0, 0, 0),
            "form" => $form->getHtml($lang), "databases" => $databases));
    }

    public function translateAction($id_space, $id_database) {

        $lang = $this->getLanguage();

        $modelClasses = new DbClass();
        $classes = $modelClasses->getForDatabase($id_database, $lang);

        //print_r($classes);
        $modelLang = new DbLang();
        $laguage = $modelLang->getAll();

        $forms = array();
        for ($i = 0; $i < count($classes); $i++) {
            $forms[] = $this->createTranslateForm($id_space, $id_database, $classes[$i], $lang);
        }

        $modelAttTr = new DbAttributTranslate();
        for ($i = 0; $i < count($classes); $i++) {
            if ($forms[$i]->check()) {
                $attids = $forms[$i]->getParameter("attid");
                //print_r($attids);
                foreach ($laguage as $langu) {
                    $modelAttTr->setAll($attids, $langu["short"], $forms[$i]->getParameter($langu["short"]));
                }
                $this->redirect("databaseconfigtranslate/" . $id_space . "/" . $id_database);
                return;
            }
        }

        $databaseModel = new DbDatabase();
        $databases = $databaseModel->getBySpace($id_space, $lang);
        $this->render(array("id_space" => $id_space, "id_database" => $id_database,
            "forms" => $forms, "menuCode" => array(0, 0, 0, 0, 1, 0, 0), "lang" => $lang,
            "databases" => $databases));
    }

    protected function createTranslateForm($id_space, $id_database, $class, $lang) {

        $form = new Form($this->request, "translateform_" . $class["id"]);
        $form->addSeparator($class["name"]);

        $modelLang = new DbLang();
        $laguage = $modelLang->getAll();

        $modelAtt = new DbAttribut();
        $atts = $modelAtt->getForClassSelect($class["id"]);
        //print_r($atts);

        $modelAttTr = new DbAttributTranslate();

        $formAdd = new FormAdd($this->request, "translateformadd_" . $class["id"]);
        $formAdd->addHidden("attid", $atts["ids"]);
        $formAdd->addText("attname", DatabaseTranslator::Attributs($lang), $atts["names"]);
        foreach ($laguage as $langu) {
            $trs = $modelAttTr->getTranslations($atts["ids"], $langu["short"]);
            $formAdd->addText($langu["short"], DatabaseTranslator::Text($lang) . " " . $langu["name"], $trs);
        }

        //$formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form->setFormAdd($formAdd);
        $form->setButtonsWidth(2, 10);
        $form->setValidationButton(CoreTranslator::Save($lang), "databaseconfigtranslate/" . $id_space . "/" . $id_database);

        return $form;
    }

    public function previewAction($id_space, $id_database) {
        $this->redirect("databaseview/" . $id_space . "/" . $id_database);
    }

    public function installAction($id_space, $id_database) {
        
        $lang = $this->getLanguage();

        $databaseModel = new DbDatabase();
        $databases = $databaseModel->getBySpace($id_space, $lang);
        
        // install form
        $formInstall = new Form($this->request, "installForm");
        $formInstall->addSeparator(DatabaseTranslator::Install_Repair_database($lang));
        $formInstall->addComment(DatabaseTranslator::Install_Txt($lang));
        $formInstall->setValidationButton(CoreTranslator::Save($lang), "databaseconfiginstall/".$id_space."/".$id_database);
        $formInstall->setButtonsWidth(2, 9);
        if ($formInstall->check()) {
            $message = "<b>Success:</b> the database have been successfully installed";
            try {
                $installModel = new DbInstaller();
                $installModel->install($id_database);

                //$modelInstMod = new CoreInstalledModules();
                //$modelInstMod->setModule("database");
            } catch (Exception $e) {
                $message = "<b>Error:</b>" . $e->getMessage();
            }
            $_SESSION["message"] = $message;
            $this->redirect("databaseconfiginstall/".$id_space."/".$id_database);
            return;
        }

        // view
        $forms = array($formInstall->getHtml($lang));
        
        $this->render(array("id_space" => $id_space, "id_database" => $id_database, "forms" => $forms, "lang" => $lang, "menuCode" => array(0, 0, 0, 0, 0, 1, 0), "lang" => $lang,
            "databases" => $databases));
    }

}

<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreInstalledModules.php';
require_once 'Modules/core/Model/CoreDashboardSection.php';
require_once 'Modules/core/Model/CoreDashboardItem.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CoredashboardController extends CoresecureController {

    private $spaceModel;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new Exception("Error 503: Permission denied");
        }
        $this->spaceModel = new CoreSpace ();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $_SESSION["openedNav"] = "config";
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        $coreConfigModel = new CoreConfig();
        $useCustomDashboard = $coreConfigModel->getParamSpace("CoreSpaceCustomDashboard", $id_space);
        
        $form = new Form($this->request, "customdashboardForm");
        $form->setTitle(CoreTranslator::ActivateCustomDashboard($lang));
        $form->addSelect("activatecustom", CoreTranslator::Choice($lang), 
                array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), 
                array(1,0), 
                $useCustomDashboard);
        $form->setValidationButton(CoreTranslator::Save($lang), "spacedashboard/".$id_space);
        
        if($form->check()){
            $coreConfigModel->setParam("CoreSpaceCustomDashboard", 
                    $form->getParameter("activatecustom"), $id_space);
            
            $this->redirect("spacedashboard/".$id_space);
            return;
        }
        
        $space = $this->spaceModel->getSpace($id_space);
        $this->render(array("id_space" => $id_space, 
            "lang" => $lang, 
            "space" => $space,
            "formHtml" => $form->getHtml($lang)));
    }

    public function sectionsAction($id_space){
        
        // security
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        // data
        $modelSection = new CoreDashboardSection();
        $data = $modelSection->getAll($id_space);
        $headers = array(
            "name" => CoreTranslator::Name($lang)
        );
        
        // view
        $table = new TableView();
        $table->addLineEditButton("spacedashboardsectionedit/".$id_space);
        $table->addDeleteButton("spacedashboardsectiondelete/".$id_space);
        $tableView = $table->view($data, $headers);
        
        $space = $this->spaceModel->getSpace($id_space);
        $this->render(array(
            "id_space" => $id_space, 
            "lang" => $lang,
            "space" => $space,
            "tableHtml" => $tableView
        ));
        
    }
    
    public function sectioneditAction($id_space, $id){
        // security
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        // data
        $modelSection = new CoreDashboardSection();
        $data = $modelSection->get($id);
        
        $form = new Form($this->request, "sectioneditActionForm");
        $form->setTitle(CoreTranslator::EditSection($lang));
        $form->addText("name", CoreTranslator::Name($lang), true, $data["name"]);
        $form->addNumber("display_order", CoreTranslator::Display_order($lang), false, $data["display_order"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "spacedashboardsectionedit/".$id_space . "/" . $id);
        
        if($form->check()){
            $modelSection->set(
                    $id, 
                    $id_space, 
                    $form->getParameter("name"), 
                    $form->getParameter("display_order")
            );
            
            $this->redirect("spacedashboardsections/".$id_space);
            return;
        }
        
        $space = $this->spaceModel->getSpace($id_space);
        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "space" => $space,
            "formHtml" => $form->getHtml($lang)
        ));
    }
    
    public function sectiondeleteAction($id_space, $id){
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $model = new CoreDashboardSection();
        $model->delete($id);
        
        $this->redirect("spacedashboardsections/".$id_space);
    }

    public function itemsAction($id_space){
        
        // security
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        // data
        $modelSection = new CoreDashboardItem();
        $data = $modelSection->getForSpace($id_space);
        $headers = array(
            "name" => CoreTranslator::Name($lang),
            "url" => CoreTranslator::Url($lang)
        );
        
        // view
        $table = new TableView();
        $table->addLineEditButton("spacedashboarditemedit/".$id_space);
        $table->addDeleteButton("spacedashboarditemdelete/".$id_space);
        $tableView = $table->view($data, $headers);
        
        $space = $this->spaceModel->getSpace($id_space);
        $this->render(array(
            "id_space" => $id_space, 
            "lang" => $lang,
            "space" => $space,
            "tableHtml" => $tableView
        ));
        
    }
    
    public function itemeditAction($id_space, $id){
        // security
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        // data
        $modelItem = new CoreDashboardItem();
        $data = $modelItem->get($id);
        
        $modelSection = new CoreDashboardSection();
        $sections = $modelSection->getForList($id_space);
        
        $modelCoreSpace = new CoreSpace();
        $roles = $modelCoreSpace->roles($lang);
        
        // form
        $form = new Form($this->request, "itemeditActionForm");
        $form->setTitle(CoreTranslator::EditSection($lang));
        
        $form->addSelectMandatory("id_section", CoreTranslator::Section($lang), $sections["names"], $sections["ids"], $data["id_section"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $data["name"]);
        $form->addText("url", CoreTranslator::Url($lang), true, $data["url"]);
        $form->addSelect("role", CoreTranslator::Role($lang), $roles["names"], $roles["ids"], $data["role"]);
        $form->addText("icon", CoreTranslator::Icon($lang), false, $data["icon"]);
        $form->addColor("color", CoreTranslator::color($lang), false, $data["color"]);
        $form->addColor("bgcolor", CoreTranslator::Background_color($lang), false, $data["bgcolor"]);
        $form->addNumber("width", CoreTranslator::Width($lang), false, $data["width"]);
        $form->addNumber("display_order", CoreTranslator::Display_order($lang), false, $data["display_order"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "spacedashboarditemedit/".$id_space . "/" . $id);
       
        
        if($form->check()){
            $modelItem->set($id, 
                    $form->getParameter("id_section"), 
                    $form->getParameter("url"), 
                    $form->getParameter("role"), 
                    $form->getParameter("name"), 
                    $form->getParameter("icon"), 
                    $form->getParameter("color"), 
                    $form->getParameter("bgcolor"), 
                    $form->getParameter("width"), 
                    $form->getParameter("display_order")
            );
            
            $this->redirect("spacedashboarditems/".$id_space);
            return;
        }
        
        $space = $this->spaceModel->getSpace($id_space);
        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "space" => $space,
            "formHtml" => $form->getHtml($lang)
        ));
    }
    
    public function itemdeleteAction($id_space, $id){
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $model = new CoreDashboardItem();
        $model->delete($id);
        
        $this->redirect("spacedashboarditems/".$id_space);
    }
}

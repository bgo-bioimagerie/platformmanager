<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';
require_once 'Modules/resources/Model/ReArea.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ReareasController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->model = new ReArea();
        //$this->checkAuthorizationMenu("resources");
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        
        $lang = $this->getLanguage();
       
        $table = new TableView();
        $table->setTitle(ResourcesTranslator::Areas($lang), 3);
        $table->addLineEditButton("reareasedit/".$id_space);
        $table->addDeleteButton("reareasdelete/".$id_space);
        
        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang)
        );
        
        $data = $this->model->getForSpace($id_space);
            
        $tableHtml = $table->view($data, $headers);
        
        $this->render(array("lang" => $lang, "id_space" => $id_space, "htmlTable" => $tableHtml));
    }
    
      /**
     * Edit form
     */
    public function editAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        
        // get belonging info
        if ($id == 0){
            $area = array("id" => 0, "name" => "", "id_space" => $id_space, "restricted" => 0);
        }
        else{
            $area = $this->model->get($id);
        }
        
        // lang
        $lang = $this->getLanguage();
        

        // form
        // build the form
        $form = new Form($this->request, "reareasedit/".$id_space);
        $form->setTitle(ResourcesTranslator::Edit_Area($lang), 3);
        $form->addHidden("id", $area["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $area["name"]);
        $form->addSelect("is_restricted", ResourcesTranslator::IsRestricted("lang"), 
                array(CoreTranslator::no($lang), CoreTranslator::yes($lang)), array(0,1), $area["restricted"]);
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "reareasedit/".$id_space."/".$id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "reareas/".$id_space);

        if ($form->check()) {
            // run the database query
            $this->model->set($form->getParameter("id"), $form->getParameter("name"), 
                    $form->getParameter("is_restricted"), $id_space);
            $this->redirect("reareas/".$id_space);
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'formHtml' => $formHtml
            ));
        }
    }
    
    public function deleteAction($id_space, $id){
        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        
        $this->model->delete($id);
        $this->redirect("reareas/".$id_space);
    }

}

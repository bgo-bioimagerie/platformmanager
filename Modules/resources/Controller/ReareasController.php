<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Controller/ResourcesBaseController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ReareasController extends ResourcesBaseController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
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
        
        return $this->render(array("data" => ["reareas" => $data], "lang" => $lang, "id_space" => $id_space, "htmlTable" => $tableHtml));
    }
    
      /**
     * Edit form
     */
    public function editAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        
        // get belonging info
        if (!$id){
            $area = array("id" => 0, "name" => "", "id_space" => $id_space, "restricted" => 0);
        }
        else{
            $area = $this->model->get($id_space, $id);
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

        $todo = $this->request->getParameterNoException('redirect');
        $validationUrl = "reareasedit/".$id_space."/".$id;
        if ($todo) {
            $validationUrl .= "?redirect=todo";
        }
        
        $form->setValidationButton(CoreTranslator::Ok($lang), $validationUrl);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "reareas/".$id_space);

        if ($form->check()) {
            // run the database query
            $id_area = $this->model->set(
                $form->getParameter("id"),
                $form->getParameter("name"), 
                $form->getParameter("is_restricted"),
                $id_space);

            $_SESSION["flash"] = ResourcesTranslator::Item_created("area", $lang);
            $_SESSION["flashClass"] = "success";

            if ($todo) {
                return $this->redirect("spaceadminedit/" . $id_space, ["showTodo" => true]);
            } else {                
                return $this->redirect("reareas/".$id_space, [], ['rearea' => ['id' => $id_area]]);
            }

        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            return $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'formHtml' => $formHtml,
                'data' => ['rearea' => $area]
            ));
        }
    }
    
    public function deleteAction($id_space, $id){
        $lang = $this->getLanguage();
        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);
        
        // check if area is linked to resources. If yes, deletion is not authorized => warn the user
        $resourceModel = new ResourceInfo();
        $linkedResources = $resourceModel->resourcesForArea($id_space, $id);

        $error = null;
        if ($linkedResources == null || empty($linkedResources)) {
            // not linked to resources, deletion is authorized
            $this->model->delete($id_space, $id);
        } else {
            // linked to resources, notify the user
            $_SESSION['flash'] = ResourcesTranslator::DeletionNotAuthorized(ResourcesTranslator::Area($lang), $lang);
            $error = 'deletionnotauthorized';
        }
        return $this->redirect("reareas/".$id_space, [], ['error' => $error]);
    }

}

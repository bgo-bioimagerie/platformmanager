<?php
require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Isotype.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class IsotypesController extends AntibodiesController {

    /**
     * User model object
     */
    private $model;

    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        $this->model = new Isotype();

    }

    // affiche la liste des Prelevements
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        // get the user list
        $isotypessArray = $this->model->getBySpace($id_space);

        $table = new TableView();
        $table->setTitle("Isotypes", 3);
        $table->addLineEditButton("isotypesedit/".$id_space."/");
        $table->addDeleteButton("isotypesdelete/".$id_space."/", "id", "nom");
        
        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($isotypessArray, $headers);
        
        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'tableHtml' => $tableHtml
        ));
    }

    public function editAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        // get isotype info
        $lang = $this->getLanguage();
        $isotypes = $this->model->get($id_space,$id);
        
        $form = new Form($this->request, "isotypeseditform");
        $form->setTitle("Modifier isotypes");
        $form->addText("nom", "nom", true, $isotypes["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "isotypesedit/".$id_space.'/'.$id);
        
        if($form->check()){
            $name = $this->request->getParameter("nom");
            if (!$id){
                $this->model->add($name, $id_space);
            }
            else{
                $this->model->edit($id, $name, $id_space);
            }
            
            $this->redirect("isotypes/".$id_space);
        }

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'formHtml' => $form->getHtml($lang)
        ));
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        // get source info
        $this->model->delete($id_space,$id);
        $this->redirect("isotypes/" . $id_space);
    }

}
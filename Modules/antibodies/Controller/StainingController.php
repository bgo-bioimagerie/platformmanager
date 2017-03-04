<?php
require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/AcStaining.php';

class StainingController extends CoresecureController {

    /**
     * User model object
     */
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new AcStaining();
        $_SESSION["openedNav"] = "antibodies";
    }

    // affiche la liste des Prelevements
    public function indexAction($id_space) {

        // get the user list
        $stainingsArray = $this->model->getBySpace($id_space);

        $table = new TableView();
        $table->setTitle("Staining", 3);
        $table->addLineEditButton("stainingedit/".$id_space."/");
        $table->addDeleteButton("stainingdelete/".$id_space."/", "id", "name");
        
        $headers = array("id" => "ID", "name" => "Nom");
        $tableHtml = $table->view($stainingsArray, $headers);
        
        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'tableHtml' => $tableHtml
        ));
    }

    public function editAction($id_space, $id) {

        // get isotype info
        $lang = $this->getLanguage();
        $staining = $this->model->get($id);
        
        $form = new Form($this->request, "stainingeditform");
        $form->setTitle("Modifier staining");
        $form->addText("nom", "nom", true, $staining["name"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "stainingedit/".$id_space.'/'.$id);
        
        if($form->check()){
            $name = $this->request->getParameter("nom");
            if ($id == 0){
                $this->model->add($name, $id_space);
            }
            else{
                $this->model->edit($id, $name, $id_space);
            }
            
            $this->redirect("staining/".$id_space);
        }

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'formHtml' => $form->getHtml($lang)
        ));
    }

    public function deleteAction($id_space, $id) {

        // get source info
        $this->model->delete($id);
        $this->redirect("staining/" . $id_space);
    }

}
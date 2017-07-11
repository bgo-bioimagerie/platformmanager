<?php
require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Enzyme.php';

class EnzymesController extends CoresecureController {

    /**
     * User model object
     */
    private $model;

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->model = new Enzyme();
        $_SESSION["openedNav"] = "antibodies";
    }

    // affiche la liste des Prelevements
    public function indexAction($id_space) {

        $lang = $this->getLanguage();
        // get the user list
        $enzymessArray = $this->model->getBySpace($id_space);

        $table = new TableView();
        $table->setTitle("Enzymes", 3);
        $table->addLineEditButton("enzymesedit/".$id_space."/");
        $table->addDeleteButton("enzymesdelete/".$id_space."/", "id", "nom");
        
        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($enzymessArray, $headers);
        
        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'tableHtml' => $tableHtml
        ));
    }

    public function editAction($id_space, $id) {
        $lang = $this->getLanguage();
        // get isotype info
        $lang = $this->getLanguage();
        $enzymes = $this->model->get($id);
        
        $form = new Form($this->request, "enzymeseditform");
        $form->setTitle("Modifier enzymes");
        $form->addText("nom", "nom", true, $enzymes["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "enzymesedit/".$id_space.'/'.$id);
        
        if($form->check()){
            $name = $this->request->getParameter("nom");
            if ($id == 0){
                $this->model->add($name, $id_space);
            }
            else{
                $this->model->edit($id, $name, $id_space);
            }
            
            $this->redirect("enzymes/".$id_space);
        }

        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'formHtml' => $form->getHtml($lang)
        ));
    }

    public function deleteAction($id_space, $id) {

        // get source info
        $this->model->delete($id);
        $this->redirect("enzymes/" . $id_space);
    }

}
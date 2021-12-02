<?php
require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Source.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class SourcesController extends AntibodiesController {

    /**
     * User model object
     */
    private $model;

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->model = new Source();
        $_SESSION["openedNav"] = "antibodies";
    }

    // affiche la liste des Prelevements
    public function indexAction($id_space) {

        // get the user list
        $sourcessArray = $this->model->getBySpace($id_space);

        $table = new TableView();
        $table->setTitle("Sources", 3);
        $table->addLineEditButton("sourcesedit/".$id_space."/");
        $table->addDeleteButton("sourcesdelete/".$id_space."/", "id", "nom");
        
        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($sourcessArray, $headers);
        
        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'tableHtml' => $tableHtml
        ));
    }

    public function editAction($id_space, $id) {

        // get isotype info
        $lang = $this->getLanguage();
        $sources = $this->model->get($id_space,$id);
        
        $form = new Form($this->request, "sourceseditform");
        $form->setTitle("Modifier sources");
        $form->addText("nom", "nom", true, $sources["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "sourcesedit/".$id_space.'/'.$id);
        
        if($form->check()){
            $name = $this->request->getParameter("nom");
            if (!$id){
                $this->model->add($name, $id_space);
            }
            else{
                $this->model->edit($id, $name, $id_space);
            }
            
            $this->redirect("sources/".$id_space);
        }

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'formHtml' => $form->getHtml($lang)
        ));
    }

    public function deleteAction($id_space, $id) {

        // get source info
        $this->model->delete($id_space,$id);
        $this->redirect("sources/" . $id_space);
    }

}
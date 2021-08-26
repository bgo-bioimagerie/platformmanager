<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Dem.php';

class DemController extends CoresecureController {

    /**
     * User model object
     */
    private $demModel;

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->demModel = new Dem();
        $_SESSION["openedNav"] = "antibodies";
    }

    // affiche la liste des Prelevements
    public function indexAction($id_space) {

        // get the user list
        $demsArray = $this->demModel->getBySpace($id_space);

        $table = new TableView();
        $table->setTitle("Dem", 3);
        $table->addLineEditButton("demedit/".$id_space."/");
        $table->addDeleteButton("demdelete/".$id_space."/", "id", "nom");
        
        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($demsArray, $headers);
        
        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'tableHtml' => $tableHtml
        ));
    }

    public function editAction($id_space, $id) {

        // get isotype info
        $lang = $this->getLanguage();
        $dem = $this->demModel->get($id_space, $id);
        
        $form = new Form($this->request, "demeditform");
        $form->setTitle("Modifier Dem");
        $form->addText("nom", "nom", true, $dem["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "demedit/".$id_space.'/'.$id);
        
        if($form->check()){
            $name = $this->request->getParameter("nom");
            if (!$id){
                $this->demModel->add($name, $id_space);
            }
            else{
                $this->demModel->edit($id, $name, $id_space);
            }
            
            $this->redirect("dem/".$id_space);
        }

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'formHtml' => $form->getHtml($lang)
        ));
    }

    public function deleteAction($id_space, $id) {

        // get source info
        $this->demModel->delete($id_space, $id);

        $this->redirect("dem/" . $id_space);
    }

}

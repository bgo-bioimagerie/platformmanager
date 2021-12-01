<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Acii.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class AciiController extends AntibodiesController {

    /**
     * User model object
     */
    private $aciiModel;

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->aciiModel = new Acii();
        $_SESSION["openedNav"] = "antibodies";
    }

    // affiche la liste des Prelevements
    public function indexAction($id_space) {

        // get the user list
        $aciisArray = $this->aciiModel->getBySpace($id_space);

        $table = new TableView();
        $table->setTitle("AcII", 3);
        $table->addLineEditButton("aciiedit/".$id_space."/");
        $table->addDeleteButton("aciidelete/".$id_space."/", "id", "nom");
        
        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($aciisArray, $headers);
        
        return $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'tableHtml' => $tableHtml
        ));
    }

    public function editAction($id_space, $id) {

        // get isotype info
        $lang = $this->getLanguage();
        $acii = $this->aciiModel->get($id_space, $id);
        
        $form = new Form($this->request, "aciieditform");
        $form->setTitle("Modifier AcII");
        $form->addText("nom", "nom", true, $acii["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "aciiedit/".$id_space.'/'.$id);
        
        if($form->check()){
            $name = $this->request->getParameter("nom");
            if (!$id){
                $this->aciiModel->add($name, $id_space);
            }
            else{
                $this->aciiModel->edit($id, $name, $id_space);
            }
            
            $this->redirect("acii/".$id_space);
        }

        return $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'formHtml' => $form->getHtml($lang)
        ));
    }

    public function deleteAction($id_space, $id) {

        // get source info
        $this->aciiModel->delete($id_space, $id);

        $this->redirect("acii/" . $id_space);
    }

}

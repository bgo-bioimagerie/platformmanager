<?php
require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Kit.php';

class KitController extends CoresecureController {

    /**
     * User model object
     */
    private $model;

    public function __construct() {
        parent::__construct();
        $this->model = new Kit();
    }

    // affiche la liste des Prelevements
    public function indexAction($id_space) {

        // get the user list
        $kitssArray = $this->model->getBySpace($id_space);

        $table = new TableView();
        $table->setTitle("Kits", 3);
        $table->addLineEditButton("kitedit/".$id_space."/");
        $table->addDeleteButton("kitdelete/".$id_space."/", "id", "nom");
        
        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($kitssArray, $headers);
        
        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'tableHtml' => $tableHtml
        ));
    }

    public function editAction($id_space, $id) {

        // get isotype info
        $lang = $this->getLanguage();
        $kits = $this->model->get($id);
        
        $form = new Form($this->request, "kitseditform");
        $form->setTitle("Modifier kits");
        $form->addText("nom", "nom", true, $kits["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "kitedit/".$id_space.'/'.$id);
        
        if($form->check()){
            $name = $this->request->getParameter("nom");
            if ($id == 0){
                $this->model->add($name, $id_space);
            }
            else{
                $this->model->edit($id, $name, $id_space);
            }
            
            $this->redirect("kit/".$id_space);
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
        $this->redirect("kit/" . $id_space);
    }

}
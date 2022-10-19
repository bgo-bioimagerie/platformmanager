<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Enzyme.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class EnzymesController extends AntibodiesController
{
    /**
     * User model object
     */
    private $model;

    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->model = new Enzyme();
    }

    // affiche la liste des Prelevements
    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
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

    public function editAction($id_space, $id)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        // get isotype info
        $lang = $this->getLanguage();
        $enzymes = $this->model->get($id_space, $id);

        $form = new Form($this->request, "enzymeseditform");
        $form->setTitle("Modifier enzymes");
        $form->addText("nom", "nom", true, $enzymes["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "enzymesedit/".$id_space.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            if (!$id) {
                $id = $this->model->add($name, $id_space);
            } else {
                $this->model->edit($id, $name, $id_space);
            }

            return $this->redirect("enzymes/".$id_space, [], ['enzyme' => ['id' => $id]]);
        }

        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'formHtml' => $form->getHtml($lang)
        ));
    }

    public function deleteAction($id_space, $id)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        // get source info
        $this->model->delete($id_space, $id);
        $this->redirect("enzymes/" . $id_space);
    }
}

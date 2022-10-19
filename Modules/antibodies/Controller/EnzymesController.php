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
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        // get the user list
        $enzymessArray = $this->model->getBySpace($idSpace);

        $table = new TableView();
        $table->setTitle("Enzymes", 3);
        $table->addLineEditButton("enzymesedit/".$idSpace."/");
        $table->addDeleteButton("enzymesdelete/".$idSpace."/", "id", "nom");

        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($enzymessArray, $headers);

        $this->render(array(
            'lang' => $lang,
            'id_space' => $idSpace,
            'tableHtml' => $tableHtml
        ));
    }

    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        // get isotype info
        $lang = $this->getLanguage();
        $enzymes = $this->model->get($idSpace, $id);

        $form = new Form($this->request, "enzymeseditform");
        $form->setTitle("Modifier enzymes");
        $form->addText("nom", "nom", true, $enzymes["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "enzymesedit/".$idSpace.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            if (!$id) {
                $id = $this->model->add($name, $idSpace);
            } else {
                $this->model->edit($id, $name, $idSpace);
            }

            return $this->redirect("enzymes/".$idSpace, [], ['enzyme' => ['id' => $id]]);
        }

        $this->render(array(
            'lang' => $lang,
            'id_space' => $idSpace,
            'formHtml' => $form->getHtml($lang)
        ));
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        // get source info
        $this->model->delete($idSpace, $id);
        $this->redirect("enzymes/" . $idSpace);
    }
}

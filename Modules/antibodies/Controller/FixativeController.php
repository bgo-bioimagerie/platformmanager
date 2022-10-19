<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Fixative.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class FixativeController extends AntibodiesController
{
    /**
     * User model object
     */
    private $model;

    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->model = new Fixative();
    }

    // affiche la liste des Prelevements
    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        // get the user list
        $fixativesArray = $this->model->getBySpace($id_space);

        $table = new TableView();
        $table->setTitle("Fixative", 3);
        $table->addLineEditButton("fixativeedit/".$id_space."/");
        $table->addDeleteButton("fixativedelete/".$id_space."/", "id", "nom");

        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($fixativesArray, $headers);

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'tableHtml' => $tableHtml
        ));
    }

    public function editAction($id_space, $id)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        // get isotype info
        $lang = $this->getLanguage();
        $fixative = $this->model->get($id_space, $id);

        $form = new Form($this->request, "fixativeeditform");
        $form->setTitle("Modifier fixative");
        $form->addText("nom", "nom", true, $fixative["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "fixativeedit/".$id_space.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            if (!$id) {
                $id = $this->model->add($name, $id_space);
            } else {
                $this->model->edit($id, $name, $id_space);
            }

            return $this->redirect("fixative/".$id_space, [], ['fixative' => ['id' => $id]]);
        }

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'formHtml' => $form->getHtml($lang)
        ));
    }

    public function deleteAction($id_space, $id)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        // get source info
        $this->model->delete($id_space, $id);
        $this->redirect("fixative/" . $id_space);
    }
}

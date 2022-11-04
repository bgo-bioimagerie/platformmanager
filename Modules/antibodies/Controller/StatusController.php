<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Status.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class StatusController extends AntibodiesController
{
    /**
     * User model object
     */
    private $model;

    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->model = new Status();
    }

    // affiche la liste des Prelevements
    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        // get the user list
        $statussArray = $this->model->getBySpace($id_space);

        $table = new TableView();
        $table->setTitle("Status", 3);
        $table->addLineEditButton("statusedit/".$id_space."/");
        $table->addDeleteButton("statusdelete/".$id_space."/", "id", "nom");

        $headers = array("id" => "ID", "nom" => "Nom", "display_order" => "Ordre d'affichage");
        $tableHtml = $table->view($statussArray, $headers);

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
        $status = $this->model->get($id_space, $id);

        $form = new Form($this->request, "statuseditform");
        $form->setTitle("Modifier status");
        $form->addText("nom", "nom", true, $status["nom"]);
        $form->addColor("color", "couleur", false, $status["color"]);
        $form->addNumber("display_order", "Ordre d'affichage", false, $status["display_order"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "statusedit/".$id_space.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            $color = $this->request->getParameter("color");
            $display_order = $this->request->getParameter("display_order");
            if (!$id) {
                $id = $this->model->add($name, $color, $display_order, $id_space);
            } else {
                $this->model->edit($id, $name, $color, $display_order, $id_space);
            }

            return $this->redirect("status/".$id_space, [], ['status' => ['id' => $id]]);
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
        $this->redirect("status/" . $id_space);
    }
}

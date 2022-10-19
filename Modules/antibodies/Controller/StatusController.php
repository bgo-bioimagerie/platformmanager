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
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        // get the user list
        $statussArray = $this->model->getBySpace($idSpace);

        $table = new TableView();
        $table->setTitle("Status", 3);
        $table->addLineEditButton("statusedit/".$idSpace."/");
        $table->addDeleteButton("statusdelete/".$idSpace."/", "id", "nom");

        $headers = array("id" => "ID", "nom" => "Nom", "display_order" => "Ordre d'affichage");
        $tableHtml = $table->view($statussArray, $headers);

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $idSpace,
            'tableHtml' => $tableHtml
        ));
    }

    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        // get isotype info
        $lang = $this->getLanguage();
        $status = $this->model->get($idSpace, $id);

        $form = new Form($this->request, "statuseditform");
        $form->setTitle("Modifier status");
        $form->addText("nom", "nom", true, $status["nom"]);
        $form->addColor("color", "couleur", false, $status["color"]);
        $form->addNumber("display_order", "Ordre d'affichage", false, $status["display_order"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "statusedit/".$idSpace.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            $color = $this->request->getParameter("color");
            $display_order = $this->request->getParameter("display_order");
            if (!$id) {
                $id = $this->model->add($name, $color, $display_order, $idSpace);
            } else {
                $this->model->edit($id, $name, $color, $display_order, $idSpace);
            }

            return $this->redirect("status/".$idSpace, [], ['status' => ['id' => $id]]);
        }

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $idSpace,
            'formHtml' => $form->getHtml($lang)
        ));
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        // get source info
        $this->model->delete($idSpace, $id);
        $this->redirect("status/" . $idSpace);
    }
}

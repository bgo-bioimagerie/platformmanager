<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/AcApplication.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class ApplicationController extends AntibodiesController
{
    /**
     * User model object
     */
    private $acapplicationModel;

    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->acapplicationModel = new AcApplication();
    }

    // affiche la liste des Prelevements
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        // get the user list
        $acapplicationsArray = $this->acapplicationModel->getBySpace($idSpace);

        $table = new TableView();
        $table->setTitle("Application", 3);
        $table->addLineEditButton("applicationedit/".$idSpace."/");
        $table->addDeleteButton("applicationdelete/".$idSpace."/", "id", "name");

        $headers = array("id" => "ID", "name" => "Nom");
        $tableHtml = $table->view($acapplicationsArray, $headers);

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
        $acapplication = $this->acapplicationModel->get($idSpace, $id);

        $form = new Form($this->request, "acapplicationeditform");
        $form->setTitle("Modifier application");
        $form->addText("nom", "nom", true, $acapplication["name"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "applicationedit/".$idSpace.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            if (!$id) {
                $id = $this->acapplicationModel->add($name, $idSpace);
            } else {
                $this->acapplicationModel->edit($id, $name, $idSpace);
            }

            return $this->redirect("application/".$idSpace, [], ['application' => ['id' => $id]]);
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
        $this->acapplicationModel->delete($idSpace, $id);

        $this->redirect("application/" . $idSpace);
    }
}

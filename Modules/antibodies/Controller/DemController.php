<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Dem.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class DemController extends AntibodiesController
{
    /**
     * User model object
     */
    private $demModel;

    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->demModel = new Dem();
    }

    // affiche la liste des Prelevements
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        // get the user list
        $demsArray = $this->demModel->getBySpace($idSpace);

        $table = new TableView();
        $table->setTitle("Dem", 3);
        $table->addLineEditButton("demedit/".$idSpace."/");
        $table->addDeleteButton("demdelete/".$idSpace."/", "id", "nom");

        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($demsArray, $headers);

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
        $dem = $this->demModel->get($idSpace, $id);

        $form = new Form($this->request, "demeditform");
        $form->setTitle("Modifier Dem");
        $form->addText("nom", "nom", true, $dem["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "demedit/".$idSpace.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            if (!$id) {
                $id = $this->demModel->add($name, $idSpace);
            } else {
                $this->demModel->edit($id, $name, $idSpace);
            }

            return $this->redirect("dem/".$idSpace, [], ['dem' => ['id' => $id]]);
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
        $this->demModel->delete($idSpace, $id);

        $this->redirect("dem/" . $idSpace);
    }
}

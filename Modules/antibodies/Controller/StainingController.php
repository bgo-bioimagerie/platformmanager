<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/AcStaining.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class StainingController extends AntibodiesController
{
    /**
     * User model object
     */
    private $model;

    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->model = new AcStaining();
    }

    // affiche la liste des Prelevements
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        // get the user list
        $stainingsArray = $this->model->getBySpace($idSpace);

        $table = new TableView();
        $table->setTitle("Staining", 3);
        $table->addLineEditButton("stainingedit/".$idSpace."/");
        $table->addDeleteButton("stainingdelete/".$idSpace."/", "id", "name");

        $headers = array("id" => "ID", "name" => "Nom");
        $tableHtml = $table->view($stainingsArray, $headers);

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
        $staining = $this->model->get($idSpace, $id);

        $form = new Form($this->request, "stainingeditform");
        $form->setTitle("Modifier staining");
        $form->addText("nom", "nom", true, $staining["name"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "stainingedit/".$idSpace.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            if (!$id) {
                $id = $this->model->add($name, $idSpace);
            } else {
                $this->model->edit($id, $name, $idSpace);
            }

            return $this->redirect("staining/".$idSpace, [], ['staining' => ['id' => $id]]);
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
        $this->redirect("staining/" . $idSpace);
    }
}

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
    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        // get the user list
        $stainingsArray = $this->model->getBySpace($id_space);

        $table = new TableView();
        $table->setTitle("Staining", 3);
        $table->addLineEditButton("stainingedit/".$id_space."/");
        $table->addDeleteButton("stainingdelete/".$id_space."/", "id", "name");

        $headers = array("id" => "ID", "name" => "Nom");
        $tableHtml = $table->view($stainingsArray, $headers);

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
        $staining = $this->model->get($id_space, $id);

        $form = new Form($this->request, "stainingeditform");
        $form->setTitle("Modifier staining");
        $form->addText("nom", "nom", true, $staining["name"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "stainingedit/".$id_space.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            if (!$id) {
                $id = $this->model->add($name, $id_space);
            } else {
                $this->model->edit($id, $name, $id_space);
            }

            return $this->redirect("staining/".$id_space, [], ['staining' => ['id' => $id]]);
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
        $this->redirect("staining/" . $id_space);
    }
}

<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Prelevement.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class PrelevementsController extends AntibodiesController
{
    /**
     * User model object
     */
    private $model;

    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->model = new Prelevement();
    }

    // affiche la liste des Prelevementss
    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        // get the user list
        $prelevementssArray = $this->model->getBySpace($id_space);

        $table = new TableView();
        $table->setTitle("Prelevements", 3);
        $table->addLineEditButton("prelevementsedit/".$id_space."/");
        $table->addDeleteButton("prelevementsdelete/".$id_space."/", "id", "nom");

        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($prelevementssArray, $headers);

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
        $prelevements = $this->model->get($id_space, $id);

        $form = new Form($this->request, "prelevementseditform");
        $form->setTitle("Modifier prelevements");
        $form->addText("nom", "nom", true, $prelevements["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "prelevementsedit/".$id_space.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            if (!$id) {
                $id = $this->model->add($name, $id_space);
            } else {
                $this->model->edit($id, $name, $id_space);
            }

            return $this->redirect("prelevements/".$id_space, [], ['prelevement' => ['id' => $id]]);
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
        $this->redirect("prelevements/" . $id_space);
    }
}

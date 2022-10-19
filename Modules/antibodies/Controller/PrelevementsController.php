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
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        // get the user list
        $prelevementssArray = $this->model->getBySpace($idSpace);

        $table = new TableView();
        $table->setTitle("Prelevements", 3);
        $table->addLineEditButton("prelevementsedit/".$idSpace."/");
        $table->addDeleteButton("prelevementsdelete/".$idSpace."/", "id", "nom");

        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($prelevementssArray, $headers);

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
        $prelevements = $this->model->get($idSpace, $id);

        $form = new Form($this->request, "prelevementseditform");
        $form->setTitle("Modifier prelevements");
        $form->addText("nom", "nom", true, $prelevements["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "prelevementsedit/".$idSpace.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            if (!$id) {
                $id = $this->model->add($name, $idSpace);
            } else {
                $this->model->edit($id, $name, $idSpace);
            }

            return $this->redirect("prelevements/".$idSpace, [], ['prelevement' => ['id' => $id]]);
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
        $this->redirect("prelevements/" . $idSpace);
    }
}

<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Linker.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class LinkerController extends AntibodiesController
{
    /**
     * User model object
     */
    private $model;

    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->model = new Linker();
    }

    // affiche la liste des Prelevements
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        // get the user list
        $linkerssArray = $this->model->getBySpace($idSpace);

        $table = new TableView();
        $table->setTitle("Linkers", 3);
        $table->addLineEditButton("linkeredit/".$idSpace."/");
        $table->addDeleteButton("linkerdelete/".$idSpace."/", "id", "nom");

        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($linkerssArray, $headers);

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
        $linkers = $this->model->get($idSpace, $id);

        $form = new Form($this->request, "linkereditform");
        $form->setTitle("Modifier linkers");
        $form->addText("nom", "nom", true, $linkers["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "linkeredit/".$idSpace.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            if (!$id) {
                $id = $this->model->add($name, $idSpace);
            } else {
                $this->model->edit($id, $name, $idSpace);
            }

            return $this->redirect("linker/".$idSpace, [], ['linker' => ['id' => $id]]);
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
        $this->redirect("linker/" . $idSpace);
    }
}

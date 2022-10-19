<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Aciinc.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class AciincController extends AntibodiesController
{
    /**
     * User model object
     */
    private $model;

    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->model = new Aciinc();
    }

    // affiche la liste des Prelevements
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        // get the user list
        $aciincsArray = $this->model->getBySpace($idSpace);

        $table = new TableView();
        $table->setTitle("AcIInc", 3);
        $table->addLineEditButton("aciincedit/".$idSpace."/");
        $table->addDeleteButton("aciincdelete/".$idSpace."/", "id", "nom");

        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($aciincsArray, $headers);

        return $this->render(array(
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
        $aciinc = $this->model->get($idSpace, $id);

        $form = new Form($this->request, "aciinceditform");
        $form->setTitle("Modifier AcIInc");
        $form->addText("nom", "nom", true, $aciinc["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "aciincedit/".$idSpace.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            if (!$id) {
                $id = $this->model->add($name, $idSpace);
            } else {
                $this->model->edit($id, $name, $idSpace);
            }

            return $this->redirect("aciinc/".$idSpace, [], ['aciinc' => ['id' => $id]]);
        }

        return $this->render(array(
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
        $this->redirect("aciinc/" . $idSpace);
    }
}

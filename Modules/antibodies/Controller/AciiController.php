<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Acii.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class AciiController extends AntibodiesController
{
    /**
     * User model object
     */
    private $aciiModel;

    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->aciiModel = new Acii();
    }

    // affiche la liste des Prelevements
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        // get the user list
        $aciisArray = $this->aciiModel->getBySpace($idSpace);

        $table = new TableView();
        $table->setTitle("AcII", 3);
        $table->addLineEditButton("aciiedit/".$idSpace."/");
        $table->addDeleteButton("aciidelete/".$idSpace."/", "id", "nom");

        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($aciisArray, $headers);

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
        $acii = $this->aciiModel->get($idSpace, $id);

        $form = new Form($this->request, "aciieditform");
        $form->setTitle("Modifier AcII");
        $form->addText("nom", "nom", true, $acii["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "aciiedit/".$idSpace.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            if (!$id) {
                $id = $this->aciiModel->add($name, $idSpace);
            } else {
                $this->aciiModel->edit($id, $name, $idSpace);
            }

            return $this->redirect("acii/".$idSpace, [], ['acii' => ['id' => $id]]);
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
        $this->aciiModel->delete($idSpace, $id);

        $this->redirect("acii/" . $idSpace);
    }
}

<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Kit.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class KitController extends AntibodiesController
{
    /**
     * User model object
     */
    private $model;

    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->model = new Kit();
    }

    // affiche la liste des Prelevements
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        // get the user list
        $kitssArray = $this->model->getBySpace($idSpace);

        $table = new TableView();
        $table->setTitle("Kits", 3);
        $table->addLineEditButton("kitedit/".$idSpace."/");
        $table->addDeleteButton("kitdelete/".$idSpace."/", "id", "nom");

        $headers = array("id" => "ID", "nom" => "Nom");
        $tableHtml = $table->view($kitssArray, $headers);

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
        $kits = $this->model->get($idSpace, $id);

        $form = new Form($this->request, "kitseditform");
        $form->setTitle("Modifier kits");
        $form->addText("nom", "nom", true, $kits["nom"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "kitedit/".$idSpace.'/'.$id);

        if ($form->check()) {
            $name = $this->request->getParameter("nom");
            if (!$id) {
                $id = $this->model->add($name, $idSpace);
            } else {
                $this->model->edit($id, $name, $idSpace);
            }

            return $this->redirect("kit/".$idSpace, [], ['kit' => ['id' => $id]]);
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
        $this->redirect("kit/" . $idSpace);
    }
}

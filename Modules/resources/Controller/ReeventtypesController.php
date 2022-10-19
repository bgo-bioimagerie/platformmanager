<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/resources/Model/ReEventType.php';
require_once 'Modules/resources/Controller/ResourcesBaseController.php';


/**
 *
 * @author sprigent
 * Controller for the home page
 */
class ReeventtypesController extends ResourcesBaseController
{
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->model = new ReEventType();

        //$this->checkAuthorizationMenu("resources");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        $table = new TableView();
        $table->setTitle(ResourcesTranslator::Event_types($lang), 3);
        $table->addLineEditButton("reeventtypesedit/".$idSpace);
        $table->addDeleteButton("reeventtypesdelete/".$idSpace);

        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang)
        );

        $categories = $this->model->getForSpace($idSpace);

        $tableHtml = $table->view($categories, $headers);

        return $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "htmlTable" => $tableHtml,
            "data" => ['reeventtypes' => $categories]
        ));
    }

    /**
     * Edit form
     */
    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);

        // get belonging info
        $site = array("id" => 0, "name" => "", "id_space" => $idSpace);
        if ($id > 0) {
            $site = $this->model->get($idSpace, $id);
        }

        // lang
        $lang = $this->getLanguage();

        // form
        // build the form
        $form = new Form($this->request, "reeventtypesedit");
        $form->setTitle(ResourcesTranslator::Edit_Event_Type($lang), 3);
        $form->addHidden("id", $site["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $site["name"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "reeventtypesedit/".$idSpace ."/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "reeventtypes/".$idSpace);

        if ($form->check()) {
            // run the database query
            $id_type = $this->model->set($form->getParameter("id"), $form->getParameter("name"), $idSpace);
            return $this->redirect("reeventtypes/".$idSpace, [], ['reeventtype' => ['id' => $id_type]]);
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            return $this->render(array(
                'id_space' => $idSpace,
                'lang' => $lang,
                'formHtml' => $formHtml,
                'data' => ['reeventtype' => $site]
            ));
        }
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);

        $this->model->delete($idSpace, $id);
        $this->redirect("reeventtypes/".$idSpace);
    }
}

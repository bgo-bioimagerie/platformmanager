<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Constants.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/resources/Model/ReState.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/resources/Controller/ResourcesBaseController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class RestatesController extends ResourcesBaseController
{
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->model = new ReState();
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
        $table->setTitle(ResourcesTranslator::States($lang), 3);
        $table->addLineEditButton("restatesedit/" . $idSpace);
        $table->addDeleteButton("restatesdelete/" . $idSpace);

        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang),
            "color" => CoreTranslator::color($lang)
        );

        $data = $this->model->getForSpace($idSpace);

        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "htmlTable" => $tableHtml,
            "data" => ['restates' => $data]
        ));
    }

    /**
     * Edit form
     */
    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);

        // get belonging info
        $data = array("id" => 0, "name" => "", "color" => Constants::COLOR_WHITE, "id_space" => $idSpace);
        if ($id > 0) {
            $data = $this->model->get($idSpace, $id);
        }

        // lang
        $lang = $this->getLanguage();

        // form
        // build the form
        $form = new Form($this->request, "restatesedit");
        $form->setTitle(ResourcesTranslator::Edit_State($lang), 3);
        $form->addHidden("id", $data["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $data["name"]);
        $form->addColor("color", CoreTranslator::color($lang), false, $data["color"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "restatesedit/" . $idSpace . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "restates/" . $idSpace);

        if ($form->check()) {
            // run the database query
            $id_state = $this->model->set($form->getParameter("id"), $form->getParameter("name"), $form->getParameter("color"), $idSpace);
            return $this->redirect("restates/" . $idSpace, [], ['restate' => ['id' => $id_state]]);
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            return $this->render(array(
                'id_space' => $idSpace,
                'lang' => $lang,
                'formHtml' => $formHtml,
                'data' => ['restate'  => $data]
            ));
        }
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);

        $this->model->delete($idSpace, $id);
        $this->redirect("restates/" . $idSpace);
    }
}

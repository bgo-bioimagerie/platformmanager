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
    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();


        $table = new TableView();
        $table->setTitle(ResourcesTranslator::States($lang), 3);
        $table->addLineEditButton("restatesedit/" . $id_space);
        $table->addDeleteButton("restatesdelete/" . $id_space);

        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang),
            "color" => CoreTranslator::color($lang)
        );

        $data = $this->model->getForSpace($id_space);

        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "htmlTable" => $tableHtml,
            "data" => ['restates' => $data]
        ));
    }

    /**
     * Edit form
     */
    public function editAction($id_space, $id)
    {
        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);

        // get belonging info
        $data = array("id" => 0, "name" => "", "color" => Constants::COLOR_WHITE, "id_space" => $id_space);
        if ($id > 0) {
            $data = $this->model->get($id_space, $id);
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

        $form->setValidationButton(CoreTranslator::Ok($lang), "restatesedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "restates/" . $id_space);

        if ($form->check()) {
            // run the database query
            $id_state = $this->model->set($form->getParameter("id"), $form->getParameter("name"), $form->getParameter("color"), $id_space);
            return $this->redirect("restates/" . $id_space, [], ['restate' => ['id' => $id_state]]);
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            return $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'formHtml' => $formHtml,
                'data' => ['restate'  => $data]
            ));
        }
    }

    public function deleteAction($id_space, $id)
    {
        $this->checkAuthorizationMenuSpace("resources", $id_space, $_SESSION["id_user"]);

        $this->model->delete($id_space, $id);
        $this->redirect("restates/" . $id_space);
    }
}

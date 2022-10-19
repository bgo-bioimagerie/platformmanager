<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';

require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/resources/Model/ReRespsStatus.php';
require_once 'Modules/resources/Controller/ResourcesBaseController.php';


/**
 *
 * @author sprigent
 * Controller for the home page
 */
class RerespsstatusController extends ResourcesBaseController
{
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->model = new ReRespsStatus();
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
        $table->setTitle(ResourcesTranslator::Resps_Status($lang), 3);
        $table->addLineEditButton("rerespsstatusedit/".$idSpace);
        $table->addDeleteButton("rerespsstatusdelete/".$idSpace);

        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang)
        );

        $data = $this->model->getForSpace($idSpace);

        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "htmlTable" => $tableHtml,
            "data" => ["rerespsstatus" => $data]
        ));
    }

    /**
     * Edit form
     */
    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);

        // get belonging info
        $data = array("id" => 0, "name" => "", "id_space" => $idSpace);
        if ($id > 0) {
            $data = $this->model->get($idSpace, $id);
        }

        // lang
        $lang = $this->getLanguage();

        // form
        // build the form
        $form = new Form($this->request, "rerespsstatusedit");
        $form->setTitle(ResourcesTranslator::Edit_Resps_status($lang), 3);
        $form->addHidden("id", $data["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $data["name"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "rerespsstatusedit/".$idSpace. "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "rerespsstatus/".$idSpace);

        if ($form->check()) {
            // run the database query
            $id_status = $this->model->set($form->getParameter("id"), $form->getParameter("name"), $idSpace);
            return $this->redirect("rerespsstatus/".$idSpace, [], ['rerespsstatus' => ['id' => $id_status]]);
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            return $this->render(array(
                "id_space" => $idSpace,
                'lang' => $lang,
                'formHtml' => $formHtml,
                'rerespsstatus' => $data
            ));
        }
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("resources", $idSpace, $_SESSION["id_user"]);

        $this->model->delete($idSpace, $id);
        $this->redirect("rerespsstatus/".$idSpace);
    }
}

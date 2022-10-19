<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/StockCabinet.php';

require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Controller/ServicesController.php';

/**
 * Manage the units (each user belongs to an unit)
 *
 * @author sprigent
 *
 */
class StockcabinetController extends ServicesController
{
    /**
     * User model object
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);

        $this->model = new StockCabinet();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        // get the user list
        $unitsArray = $this->model->getAll($idSpace);

        $table = new TableView();
        $table->setTitle(ServicesTranslator::Cabinets($lang), 3);
        $table->addLineEditButton("stockcabinetedit/" . $idSpace);
        $table->addDeleteButton("stockcabinetdelete/" . $idSpace);
        $tableHtml = $table->view($unitsArray, array(
            "room_number" => ServicesTranslator::RoomNumber($lang),
            "name" => ServicesTranslator::Cabinet($lang),
            "id" => "ID"
        ));

        $this->render(array(
            'id_space' => $idSpace,
            'lang' => $lang,
            'tableHtml' => $tableHtml
        ));
    }

    /**
     * Edit an unit form
     */
    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);

        // get belonging info
        $unit = array("id" => 0, "name" => "", "room_number" => "");
        if ($id > 0) {
            $unit = $this->model->getOne($idSpace, $id);
        }

        // lang
        $lang = $this->getLanguage();

        // form
        // build the form
        $form = new Form($this->request, "stockcabineteditform");
        $form->setTitle(ServicesTranslator::Cabinet($lang), 3);
        $form->addHidden("id", $unit["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $unit["name"]);
        $form->addText("room_number", ServicesTranslator::RoomNumber($lang), false, $unit["room_number"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "stockcabinetedit/" . $idSpace . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "stockcabinets/" . $idSpace);

        if ($form->check()) {
            // run the database query
            $cab_id = $this->model->set($idSpace, $form->getParameter("id"), $form->getParameter("name"), $form->getParameter("room_number"));
            return $this->redirect("stockcabinets/" . $idSpace, [], ['cabinet' => ['id' => $cab_id]]);
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // view
            return $this->render(array(
                'id_space' => $idSpace,
                'lang' => $lang,
                'formHtml' => $formHtml,
                'data' => ['cabinet' => $unit]
            ));
        }
    }

    /**
     * Remove an unit query to database
     */
    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);

        $this->model->delete($idSpace, $id);
        $this->redirect("stockcabinets/" . $idSpace);
    }
}

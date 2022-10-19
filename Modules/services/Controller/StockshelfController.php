<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/StockCabinet.php';
require_once 'Modules/services/Model/StockShelf.php';

require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Controller/ServicesController.php';

/**
 * Manage the units (each user belongs to an unit)
 *
 * @author sprigent
 *
 */
class StockshelfController extends ServicesController
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

        $this->model = new StockShelf();
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
        $table->setTitle(ServicesTranslator::Shelfs($lang), 3);
        $table->addLineEditButton("stockshelfedit/" . $idSpace);
        $table->addDeleteButton("stockshelfdelete/" . $idSpace);
        $tableHtml = $table->view($unitsArray, array(
            "room" => ServicesTranslator::RoomNumber($lang),
            "cabinet" => ServicesTranslator::Cabinet($lang),
            "name" => ServicesTranslator::Shelf($lang),
            "id" => "ID",
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
        $unit = array("id" => 0, "name" => "", "id_cabinet" => 0);
        if ($id > 0) {
            $unit = $this->model->getOne($idSpace, $id);
        }

        // lang
        $lang = $this->getLanguage();


        $modelCabinet = new StockCabinet();
        $cabinets = $modelCabinet->getForList($idSpace);

        // form
        // build the form
        $form = new Form($this->request, "stockshelfeditform");
        $form->setTitle(ServicesTranslator::Shelf($lang), 3);
        $form->addHidden("id", $unit["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $unit["name"]);
        $form->addSelect("id_cabinet", ServicesTranslator::Cabinet($lang), $cabinets["names"], $cabinets["ids"], $unit["id_cabinet"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "stockshelfedit/" . $idSpace . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "stockshelfs/" . $idSpace);

        if ($form->check()) {
            // run the database query
            $shelf_id = $this->model->set($idSpace, $form->getParameter("id"), $form->getParameter("name"), $form->getParameter("id_cabinet"));
            return $this->redirect("stockshelfs/" . $idSpace, [], ['shelf' => ['id' => $shelf_id]]);
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // view
            return $this->render(array(
                'id_space' => $idSpace,
                'lang' => $lang,
                'formHtml' => $formHtml,
                'data' => ['shelf' => $unit]
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
        $this->redirect("stockshelfs/" . $idSpace);
    }
}

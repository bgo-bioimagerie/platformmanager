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
    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        // get the user list
        $unitsArray = $this->model->getAll($id_space);

        $table = new TableView();
        $table->setTitle(ServicesTranslator::Shelfs($lang), 3);
        $table->addLineEditButton("stockshelfedit/" . $id_space);
        $table->addDeleteButton("stockshelfdelete/" . $id_space);
        $tableHtml = $table->view($unitsArray, array(
            "room" => ServicesTranslator::RoomNumber($lang),
            "cabinet" => ServicesTranslator::Cabinet($lang),
            "name" => ServicesTranslator::Shelf($lang),
            "id" => "ID",
        ));

        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'tableHtml' => $tableHtml
        ));
    }

    /**
     * Edit an unit form
     */
    public function editAction($id_space, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        // get belonging info
        $unit = array("id" => 0, "name" => "", "id_cabinet" => 0);
        if ($id > 0) {
            $unit = $this->model->getOne($id_space, $id);
        }

        // lang
        $lang = $this->getLanguage();


        $modelCabinet = new StockCabinet();
        $cabinets = $modelCabinet->getForList($id_space);

        // form
        // build the form
        $form = new Form($this->request, "stockshelfeditform");
        $form->setTitle(ServicesTranslator::Shelf($lang), 3);
        $form->addHidden("id", $unit["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $unit["name"]);
        $form->addSelect("id_cabinet", ServicesTranslator::Cabinet($lang), $cabinets["names"], $cabinets["ids"], $unit["id_cabinet"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "stockshelfedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "stockshelfs/" . $id_space);

        if ($form->check()) {
            // run the database query
            $shelf_id = $this->model->set($id_space, $form->getParameter("id"), $form->getParameter("name"), $form->getParameter("id_cabinet"));
            return $this->redirect("stockshelfs/" . $id_space, [], ['shelf' => ['id' => $shelf_id]]);
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // view
            return $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'formHtml' => $formHtml,
                'data' => ['shelf' => $unit]
            ));
        }
    }

    /**
     * Remove an unit query to database
     */
    public function deleteAction($id_space, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $this->model->delete($id_space, $id);
        $this->redirect("stockshelfs/" . $id_space);
    }
}

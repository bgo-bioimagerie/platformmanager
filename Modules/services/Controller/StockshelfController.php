<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/StockCabinet.php';
require_once 'Modules/services/Model/Stockshelf.php';

require_once 'Modules/services/Model/ServicesTranslator.php';

/**
 * Manage the units (each user belongs to an unit)
 * 
 * @author sprigent
 *
 */
class StockshelfController extends CoresecureController {

    /**
     * User model object
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        
        $this->model = new StockShelf ();
        $_SESSION["openedNav"] = "services";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        // get the user list
        $unitsArray = $this->model->getAll($id_space);

        $table = new TableView();
        $table->setTitle(ServicesTranslator::Shelfs($lang), 3);
        $table->addLineEditButton("stockshelfedit/" . $id_space);
        $table->addDeleteButton("stockshelfdelete/" . $id_space);
        $tableHtml = $table->view($unitsArray, array(
            "id" => "ID", 
            "name" => CoreTranslator::Name($lang), 
            "cabinet" => ServicesTranslator::Cabinet($lang)
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
    public function editAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        // get belonging info
        $unit = array("id" => 0, "name" => "", "id_cabinet" => 0);
        if ($id > 0) {
            $unit = $this->model->getOne($id);
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
            $this->model->set($form->getParameter("id"), $form->getParameter("name"), $form->getParameter("id_cabinet")); 
            $this->redirect("stockshelfs/" . $id_space);
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // view
            $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'formHtml' => $formHtml
            ));
        }
    }

    /**
     * Remove an unit query to database
     */
    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $this->model->delete($id);
        $this->redirect("stockshelfs/" . $id_space);
    }

}

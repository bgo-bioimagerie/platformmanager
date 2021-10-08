<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/StockCabinet.php';

require_once 'Modules/services/Model/ServicesTranslator.php';

/**
 * Manage the units (each user belongs to an unit)
 * 
 * @author sprigent
 *
 */
class StockcabinetController extends CoresecureController {

    /**
     * User model object
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        
        $this->model = new StockCabinet ();
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
        $table->setTitle(ServicesTranslator::Cabinets($lang), 3);
        $table->addLineEditButton("stockcabinetedit/" . $id_space);
        $table->addDeleteButton("stockcabinetdelete/" . $id_space);
        $tableHtml = $table->view($unitsArray, array(
            "room_number" => ServicesTranslator::RoomNumber($lang),
            "name" => ServicesTranslator::Cabinet($lang), 
            "id" => "ID" 
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
        $unit = array("id" => 0, "name" => "", "room_number" => "");
        if ($id > 0) {
            $unit = $this->model->getOne($id_space ,$id);
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
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "stockcabinetedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "stockcabinets/" . $id_space);

        if ($form->check()) {
            // run the database query
            $this->model->set($id_space, $form->getParameter("id"), $form->getParameter("name"), $form->getParameter("room_number")); 
            $this->redirect("stockcabinets/" . $id_space);
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

        $this->model->delete($id_space ,$id);
        $this->redirect("stockcabinets/" . $id_space);
    }

}

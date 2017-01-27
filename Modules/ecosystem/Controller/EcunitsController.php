<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/ecosystem/Model/EcUnit.php';
require_once 'Modules/ecosystem/Model/EcBelonging.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';

/**
 * Manage the units (each user belongs to an unit)
 * 
 * @author sprigent
 *
 */
class EcunitsController extends CoresecureController {

    /**
     * User model object
     */
    private $unitModel;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("users/institutions");
        $this->unitModel = new EcUnit ();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("users/institutions", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        // get sort action
        $sortentry = "id";

        // get the user list
        $unitsArray = $this->unitModel->getUnits($id_space, $sortentry);

        $table = new TableView();
        $table->setTitle(CoreTranslator::Units($lang), 3);
        $table->addLineEditButton("ecunitsedit/" . $id_space);
        $table->addDeleteButton("ecunitsdelete/" . $id_space);
        $tableHtml = $table->view($unitsArray, array("id" => "ID", "name" => CoreTranslator::Name($lang), "address" => CoreTranslator::Address($lang), "belonging" => CoreTranslator::Belonging($lang)));

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
        $this->checkAuthorizationMenuSpace("users/institutions", $id_space, $_SESSION["id_user"]);

        // get belonging info
        $unit = array("id" => 0, "name" => "", "address" => "", "id_belonging" => 0);
        if ($id > 0) {
            $unit = $this->unitModel->getInfo($id);
        }

        // belongings
        $modelBelonging = new EcBelonging();
        $belongingsList = $modelBelonging->getForList($id_space);
        //$belongingsid = $modelBelonging->getIds();
        //$belongingsnames = $modelBelonging->getNames();

        // lang
        $lang = $this->getLanguage();

        // form
        // build the form
        $form = new Form($this->request, "Coreunits/edit");
        $form->setTitle(CoreTranslator::Edit_Unit($lang), 3);
        $form->addHidden("id", $unit["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $unit["name"]);
        $form->addTextArea("address", CoreTranslator::Address($lang), false, $unit["address"]);
        
        $id_belonging = $this->unitModel->getBelonging($unit["id"], $id_space);
        $form->addSelect("id_belonging", CoreTranslator::Belonging($lang), $belongingsList["names"], $belongingsList["ids"], $id_belonging);

        $form->setValidationButton(CoreTranslator::Ok($lang), "ecunitsedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "ecunits/" . $id_space);

        if ($form->check()) {
            // run the database query
            $model = new EcUnit();
            $unit_id = $model->set($form->getParameter("id"), $form->getParameter("name"), $form->getParameter("address"));
            $model->setBelonging($id_space, $form->getParameter("id_belonging"), $unit_id);    
            
            $this->redirect("ecunits/" . $id_space);
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
        $this->checkAuthorizationMenuSpace("users/institutions", $id_space, $_SESSION["id_user"]);

        $this->unitModel->delete($id);
        $this->redirect("ecunits/" . $id_space);
    }

}

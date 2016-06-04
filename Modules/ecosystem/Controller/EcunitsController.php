<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/ecosystem/Model/EcUnit.php';
require_once 'Modules/ecosystem/Model/EcBelonging.php';

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
        $this->unitModel = new EcUnit ();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction() {

        $lang = $this->getLanguage();

        // get sort action
        $sortentry = "id";
        if ($this->request->isParameterNotEmpty('actionid')) {
            $sortentry = $this->request->getParameter("actionid");
        }

        // get the user list
        $unitsArray = $this->unitModel->getUnits($sortentry);

        $table = new TableView();
        $table->setTitle(CoreTranslator::Units($lang));
        $table->addLineEditButton("ecunitsedit");
        $table->addDeleteButton("ecunitsdelete");
        $tableHtml = $table->view($unitsArray, array("id" => "ID", "name" => CoreTranslator::Name($lang), "address" => CoreTranslator::Address($lang), "belonging" => CoreTranslator::Belonging($lang)));

        $this->render(array(
            'lang' => $lang,
            'tableHtml' => $tableHtml
        ));
    }

    /**
     * Edit an unit form
     */
    public function editAction($id) {
        
        // get belonging info
        $unit = array("id" => 0, "name" => "", "address" => "", "id_belonging" => 0);
        if ($id > 0) {
            $unit = $this->unitModel->getInfo($id);
        }

        // belongings
        $modelBelonging = new EcBelonging();
        $belongingsid = $modelBelonging->getIds();
        $belongingsnames = $modelBelonging->getNames();

        // lang
        $lang = $this->getLanguage();

        // form
        // build the form
        $form = new Form($this->request, "Coreunits/edit");
        $form->setTitle(CoreTranslator::Edit_Unit($lang));
        $form->addHidden("id", $unit["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $unit["name"]);
        $form->addTextArea("address", CoreTranslator::Address($lang), false, $unit["address"]);
        $form->addSelect("id_belonging", CoreTranslator::Belonging($lang), $belongingsnames, $belongingsid, $unit["id_belonging"]);

        $form->setValidationButton(CoreTranslator::Ok($lang), "ecunitsedit/".$id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "ecunits");


        if ($form->check()) {
            // run the database query
            $model = new EcUnit();
            $model->set($form->getParameter("id"), $form->getParameter("name"), $form->getParameter("address"), $form->getParameter("id_belonging"));

            $this->redirect("ecunits");
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            $this->render(array(
                'lang' => $lang,
                'formHtml' => $formHtml
            ));
        }
    }

    /**
     * Remove an unit query to database
     */
    public function deleteAction($id) {

        $this->unitModel->delete($id);
        $this->redirect("ecunits");
    }

}

<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/ecosystem/Model/EcBelonging.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';

/**
 * Manage the belongings (each user belongs to an belonging)
 * 
 * @author sprigent
 *
 */
class EcbelongingsController extends CoresecureController {

    /**
     * User model object
     */
    private $belongingModel;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->checkAuthorizationMenu("users/institutions");
        $this->belongingModel = new EcBelonging();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction() {

        $lang = $this->getLanguage();

        // get the user list
        $belongingsArray = $this->belongingModel->getbelongings("name");
        for ($i = 0; $i < count($belongingsArray); $i++) {
            if ($belongingsArray[$i]["type"] == 1) {
                $belongingsArray[$i]["type"] = CoreTranslator::Academic($lang);
            } else {
                $belongingsArray[$i]["type"] = CoreTranslator::Company($lang);
            }
        }

        $table = new TableView();
        $table->setTitle(CoreTranslator::belongings($lang));
        $table->addLineEditButton("ecbelongingsedit");
        $table->addDeleteButton("ecbelongingsdelete");
        $table->setColorIndexes(array("color" => "color"));
        $tableHtml = $table->view($belongingsArray, array("id" => "ID", "name" => CoreTranslator::Name($lang),
            "color" => CoreTranslator::color($lang), "type" => CoreTranslator::type($lang)
        ));


        $this->render(array(
            'lang' => $lang,
            'tableHtml' => $tableHtml
        ));
    }

    /**
     * Edit an belonging form
     */
    public function editAction($id) {


        // get belonging info
        $belonging = array("id" => 0, "name" => "", "color" => "#ffffff", "type" => 1);
        if ($id > 0) {
            $belonging = $this->belongingModel->getInfo($id);
        }

        // lang
        $lang = $this->getLanguage();

        // form
        // build the form
        $form = new Form($this->request, "Corebelongings/edit");
        $form->setTitle(EcosystemTranslator::Edit_belonging($lang));
        $form->addHidden("id", $belonging["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $belonging["name"]);
        $form->addColor("color", CoreTranslator::color($lang), false, $belonging["color"]);

        $choices = array(CoreTranslator::Academic($lang), CoreTranslator::Company($lang));
        $choicesid = array(1, 2);
        $form->addSelect("type", CoreTranslator::type($lang), $choices, $choicesid, $belonging["type"]);
        $form->setValidationButton(EcosystemTranslator::Ok($lang), "ecbelongingsedit/".$id);
        $form->setCancelButton(EcosystemTranslator::Cancel($lang), "ecbelongings");
        $form->setButtonsWidth(2, 9);

        if ($form->check()) {
            // run the database query
            $model = new EcBelonging();
            $model->set($form->getParameter("id"), $form->getParameter("name"), $form->getParameter("color"), $form->getParameter("type"));

            $this->redirect("ecbelongings");
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
     * Remove an belonging query to database
     */
    public function deleteAction($id) {
        
        $this->belongingModel->delete($id);
        $this->redirect("ecbelongings");
    }

}

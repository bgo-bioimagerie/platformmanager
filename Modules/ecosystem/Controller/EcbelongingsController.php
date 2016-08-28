<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
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
        //$this->checkAuthorizationMenu("users/institutions");
        $this->belongingModel = new EcBelonging();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("users/institutions", $id_space, $_SESSION["id_user"]);
        
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
        $table->addLineEditButton("ecbelongingsedit/".$id_space);
        $table->addDeleteButton("ecbelongingsdelete/".$id_space);
        $table->setColorIndexes(array("color" => "color"));
        $tableHtml = $table->view($belongingsArray, array("id" => "ID", "name" => CoreTranslator::Name($lang),
            "color" => CoreTranslator::color($lang), "type" => CoreTranslator::type($lang)
        ));


        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'tableHtml' => $tableHtml
        ));
    }

    /**
     * Edit an belonging form
     */
    public function editAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("users/institutions", $id_space, $_SESSION["id_user"]);
        
        // get belonging info
        $belonging = array("id" => 0, "name" => "", "color" => "#ffffff", "type" => 1);
        if ($id > 0) {
            $belonging = $this->belongingModel->getInfo($id);
        }

        // lang
        $lang = $this->getLanguage();

        // form
        // build the form
        $form = new Form($this->request, "corebelongings/edit");
        $form->setTitle(EcosystemTranslator::Edit_belonging($lang));
        $form->addHidden("id", $belonging["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $belonging["name"]);
        $form->addColor("color", CoreTranslator::color($lang), false, $belonging["color"]);

        $choices = array(CoreTranslator::Academic($lang), CoreTranslator::Company($lang));
        $choicesid = array(1, 2);
        $form->addSelect("type", CoreTranslator::type($lang), $choices, $choicesid, $belonging["type"]);
        $form->setValidationButton(EcosystemTranslator::Ok($lang), "ecbelongingsedit/".$id_space."/".$id);
        $form->setCancelButton(EcosystemTranslator::Cancel($lang), "ecbelongings/".$id_space);
        $form->setButtonsWidth(2, 9);

        if ($form->check()) {
            // run the database query
            $model = new EcBelonging();
            $model->set($form->getParameter("id"), $form->getParameter("name"), $form->getParameter("color"), $form->getParameter("type"));

            $this->redirect("ecbelongings/".$id_space);
        } else {
            // set the view
            $formHtml = $form->getHtml();
            // view
            $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'formHtml' => $formHtml
            ));
        }
    }

    /**
     * Remove an belonging query to database
     */
    public function deleteAction($id_space, $id) {
        
        $this->checkAuthorizationMenuSpace("users/institutions", $id_space, $_SESSION["id_user"]);
        
        $this->belongingModel->delete($id);
        $this->redirect("ecbelongings/".$id_space);
    }

}

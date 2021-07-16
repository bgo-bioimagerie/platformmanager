<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';
require_once 'Modules/breeding/Model/BrProduct.php';
require_once 'Modules/breeding/Model/BrBatch.php';
require_once 'Modules/breeding/Model/BrLosse.php';
require_once 'Modules/breeding/Model/BrLosseType.php';
require_once 'Modules/breeding/Form/BatchInfoForm.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class BreedinglossesController extends CoresecureController {

    /**
     * User model object
     */
    private $model;
    private $modelBatch;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->model = new BrLosse ();
        $this->modelBatch = new BrBatch();
        $_SESSION["openedNav"] = "breeding";
    }

    /**
     * Edit
     */
    public function editAction($id_space, $id_batch, $id) {
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        $data = $this->model->get($id);
        $batch = $this->modelBatch->get($id_batch);

        $modelLosseTypes = new BrLosseType();
        $losseTypes = $modelLosseTypes->getForList($id_space);

        $form = new Form($this->request, "brlosseseditform");
        $form->addDate("date", CoreTranslator::Date($lang), true, CoreTranslator::dateFromEn($data["date"], $lang));
        $form->addNumber("quantity", BreedingTranslator::Quantity($lang), true, $data["quantity"]);
        $form->addTextArea("comment", BreedingTranslator::Comment($lang), false, $data["comment"]);
        $form->addSelect("id_type", BreedingTranslator::lossetype($lang), $losseTypes["names"], $losseTypes["ids"], $data["id_type"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "brlosseedit/" . $id_space . "/" . $id_batch . "/" . $id);
        $form->setButtonsWidth(2, 9);

        if ($form->check()) {
            $id = $this->model->set($id, $id_space, $id_batch, CoreTranslator::dateToEn($form->getParameter("date"), $lang), $_SESSION["id_user"], $form->getParameter("quantity"), $form->getParameter("comment"), $form->getParameter("id_type")
            );

            //$_SESSION["message"] = BreedingTranslator::Data_has_been_saved($lang);
            
            $modelBatch = new BrBatch();
            $modelBatch->updateQuantity($id_batch);
            
            $this->redirect("brmoves/" . $id_space . "/" . $id_batch);
            return;
        }

        // render the View
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'formHtml' => $form->getHtml($lang),
            'batch' => $batch,
            'activTab' => "infos"
        ));
    }

    /**
     * Remove
     */
    public function deleteAction($id_space, $id) {
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);

        // query to delete the provider
        $this->model->delete($id);

        // after the provider is deleted we redirect to the providers list page
        $this->redirect("brbatchs/" . $id_space);
    }

}

<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';
require_once 'Modules/breeding/Model/BrCategory.php';
require_once 'Modules/breeding/Model/BrBatch.php';


/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class BreedingsexingController extends CoresecureController {
    
    /**
     * User model object
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->model = new BrCategory ();
        $_SESSION["openedNav"] = "breeding";
    }

    /**
     * Edit a provider form
     */
    public function indexAction($id_space, $id_batch) {
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        $modelBatch = new BrBatch();
        $batch = $modelBatch->get($id_batch);
        
        // form
        // build the form
        $form = new Form($this->request, "pricing/edit");
        $form->setTitle(BreedingTranslator::Sexing($lang), 3);
        $form->addNumber("quantity_female", BreedingTranslator::QuantityFemale($lang), true, 0);
        $form->addNumber("quantity_male", BreedingTranslator::QuantityMale($lang), true, 0);
        
        $form->setValidationButton(CoreTranslator::Ok($lang), "brsexing/" . $id_space . "/" . $id_batch);
        $form->setButtonsWidth(4, 8);

        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            
            $modelBatch = new BrBatch();
            $modelBatch->sexing($id_space, $id_batch, 
                    $form->getParameter("quantity_female"),
                    $form->getParameter("quantity_male")
                    );
            
            // after the provider is saved we redirect to the providers list page
            $this->redirect("brbatchedit/" . $id_space . "/" . $id_batch);
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // render the view
            $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'formHtml' => $formHtml,
                'activTab' => 'sexing',
                'batch' => $batch
            ));
        }
    }

}

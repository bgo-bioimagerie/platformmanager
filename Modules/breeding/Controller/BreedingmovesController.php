<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';
require_once 'Modules/breeding/Model/BrLosseType.php';
require_once 'Modules/breeding/Model/BrMoves.php';
require_once 'Modules/breeding/Model/BrBatch.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class BreedingmovesController extends CoresecureController {
   
    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $_SESSION["openedNav"] = "breeding";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($id_space, $id_batch) {
        
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // Query to the database
        $modelMoves = new BrMoves();
        $moves = $modelMoves->getForBatch($id_batch, $lang);
        
        //print_r($moves);

        // table
        $table = new TableView();
        $table->addLineEditButton("brmoveedit/" . $id_space);
        $table->addDeleteButton("brmovedelete/" . $id_space, "id", "date");
        $tableHtml = $table->view($moves, array(
            "date" => CoreTranslator::Date($lang),
            "quantity" => BreedingTranslator::Quantity($lang),
            "type" => BreedingTranslator::Type($lang),
            "details" => BreedingTranslator::Details($lang),
            "comment" => BreedingTranslator::Comment($lang)
        ));

        // render the View
        $modelBatch = new BrBatch();
        $batch = $modelBatch->get($id_batch);
        
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'id_batch' => $id_batch,
            'activTab' => "moves",
            'tableHtml' => $tableHtml,
            'batch' => $batch
        ));
    }

}

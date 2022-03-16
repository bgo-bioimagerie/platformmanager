<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

require_once 'Modules/booking/Model/BkNightWE.php';

require_once 'Modules/clients/Model/ClPricing.php';
require_once 'Modules/booking/Controller/BookingsettingsController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingnightweController extends BookingsettingsController {

    public function indexAction($id_space){
        
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        
        $lang = $this->getLanguage();
        
        // get the core belongings
        $modelBelonging = new ClPricing();
        $belongings = $modelBelonging->getAll($id_space);
        
        //print_r($belongings);
        
        // get the sygrrig pricing
        $modelPricing = new BkNightWE();
        $modelPricing->addBelongingIfNotExists($id_space, $belongings);
        $pricingArray = $modelPricing->getSpacePrices($id_space, "id");

        // prepare view
        for ($i = 0; $i < count($pricingArray); $i++) {
            $pricingArray[$i]["name"] = $modelBelonging->getName($id_space, $pricingArray[$i]["id_belonging"]);
            if ($pricingArray[$i]["tarif_unique"] == 1) {
                $pricingArray[$i]["tarif_unique"] = CoreTranslator::yes($lang);
            } else {
                $pricingArray[$i]["tarif_unique"] = CoreTranslator::no($lang);
            }
            if ($pricingArray[$i]["tarif_night"] == 1) {
                $pricingArray[$i]["tarif_night"] = CoreTranslator::yes($lang);
            } else {
                $pricingArray[$i]["tarif_night"] = CoreTranslator::no($lang);
            }
            if ($pricingArray[$i]["tarif_we"] == 1) {
                $pricingArray[$i]["tarif_we"] = CoreTranslator::yes($lang);
            } else {
                $pricingArray[$i]["tarif_we"] = CoreTranslator::no($lang);
            }
        }

        $table = new TableView ();

        $table->setTitle(BookingTranslator::Nightwe($lang), 3);
        //$table->ignoreEntry("id", 1);
        $table->addLineEditButton("bookingnightweedit/".$id_space, "id_belonging");
        $table->addDeleteButton("bookingnightwedelete/".$id_space);

        $tableContent = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang),
            "tarif_unique" => BookingTranslator::Unique_price($lang),
            "tarif_night" => BookingTranslator::Price_night($lang),
            "tarif_we" => BookingTranslator::Price_weekend($lang)
        );
        $tableHtml = $table->view($pricingArray, $tableContent);
        
        $this->render(array("id_space" => $id_space, "lang" => $lang, 'tableHtml' => $tableHtml, 'data' => ['pricings' => $pricingArray]));
    }
    
    public function editAction($id_space, $id){
        
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        $modelPricing = new BkNightWE();
        $pricing = $modelPricing->getPricing($id, $id_space);

        $modelBelonging = new ClPricing();
        $pricing["name"] = $modelBelonging->getName($id_space, $id);

        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'pricing' => $pricing
        ));
    }

    /**
     * Query to edit a pricing
     */
    public function editqueryAction($id_space) {

        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);

        // get form variables
        $id = $this->request->getParameter("id");
        $tarif_unique = $this->request->getParameter("tarif_unique");
        $tarif_nuit = $this->request->getParameter("tarif_night");
        $night_start = $this->request->getParameter("night_start");
        $night_end = $this->request->getParameter("night_end");
        $tarif_we = $this->request->getParameter("tarif_we");

        $lundi = $this->request->getParameterNoException("lundi");
        $mardi = $this->request->getParameterNoException("mardi");
        $mercredi = $this->request->getParameterNoException("mercredi");
        $jeudi = $this->request->getParameterNoException("jeudi");
        $vendredi = $this->request->getParameterNoException("vendredi");
        $samedi = $this->request->getParameterNoException("samedi");
        $dimanche = $this->request->getParameterNoException("dimanche");

        if ($lundi != "") {
            $lundi = "1";
        } else {
            $lundi = "0";
        }
        if ($mardi != "") {
            $mardi = "1";
        } else {
            $mardi = "0";
        }
        if ($mercredi != "") {
            $mercredi = "1";
        } else {
            $mercredi = "0";
        }
        if ($jeudi != "") {
            $jeudi = "1";
        } else {
            $jeudi = "0";
        }
        if ($vendredi != "") {
            $vendredi = "1";
        } else {
            $vendredi = "0";
        }
        if ($samedi != "") {
            $samedi = "1";
        } else {
            $samedi = "0";
        }
        if ($dimanche != "") {
            $dimanche = "1";
        } else {
            $dimanche = "0";
        }

        $we_char = $lundi . "," . $mardi . "," . $mercredi . "," . $jeudi . "," . $vendredi . "," . $samedi . "," . $dimanche;

        $modelPricing = new BkNightWE();
        $modelPricing->editPricing($id, $id_space, $tarif_unique, $tarif_nuit, $night_start, $night_end, $tarif_we, $we_char);

        $this->redirect("bookingnightwe/".$id_space);
    }

}

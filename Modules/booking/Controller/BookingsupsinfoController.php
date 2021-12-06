<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkCalSupInfo.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/core/Model/CoreVirtual.php';
require_once 'Modules/booking/Controller/BookingsettingsController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingsupsinfoController extends BookingsettingsController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        //$this->checkAuthorizationMenu("bookingsettings");

    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        $modelResource = new ResourceInfo();
        $resources = $modelResource->getForSpace($id_space);
        $choicesR = array();
        $choicesRid = array();
        foreach ($resources as $res) {
            $choicesR[] = $res["name"];
            $choicesRid[] = $res["id"];
        }

        $modelSups = new BkCalSupinfo();
        $sups = $modelSups->getForSpace($id_space, "id_resource");
        $supsIds = array();
        $supsIdsRes = array();
        $supsNames = array();
        $supsMandatories = array();
        foreach ($sups as $p) {
            $supsIds[] = $p["id_supinfo"];
            $supsIdsRes[] = $p["id_resource"];
            $supsNames[] = $p["name"];
            $supsMandatories[] = $p["mandatory"];
        }

        $form = new Form($this->request, "supsForm");
        $form->setTitle(BookingTranslator::Supplementaries($lang));

        $formAdd = new FormAdd($this->request, "supsAddForm");
        $formAdd->addHidden("id_sups", $supsIds);
        $formAdd->addSelect("id_resources", BookingTranslator::Resource($lang), $choicesR, $choicesRid, $supsIdsRes);
        $formAdd->addText("names", CoreTranslator::Name($lang), $supsNames);
        $formAdd->addSelect("mandatory", BookingTranslator::Is_mandatory($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $supsMandatories);

        $formAdd->setButtonsNames(CoreTranslator::Add(), CoreTranslator::Delete($lang));
        $form->setFormAdd($formAdd);
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingsupsinfo/".$id_space);
        $form->setButtonsWidth(2, 9);

        if ($form->check()) {
            $supID = $this->request->getParameterNoException("id_sups");
            $supResource = $this->request->getParameterNoException("id_resources");
            $supName = $this->request->getParameterNoException("names");
            $supMandatory = $this->request->getParameterNoException("mandatory");

            $packs = [];
            for ($p = 0; $p < count($supID); $p++) {
                if ($supName[$p] != "" && $supID[$p]) {
                   $packs[$supName[$p]] = $supID[$p];
                }
            }
            for ($p = 0; $p < count($supID); $p++) {
                if (!$supID[$p]) {
                    // If package id not set, use from known packages
                    if(isset($packs[$supName[$p]])) {
                        $supID[$p] = $packs[$supName[$p]];
                    } else {
                        // Or create a new package
                       $cvm = new CoreVirtual();
                       $vid = $cvm->new('supinfo');
                       $supID[$p] = $vid;
                       $packs[$supName[$p]] = $vid;
                   }
                }
                $modelSups->setCalSupInfo($id_space, $supID[$p], $supResource[$p], $supName[$p], $supMandatory[$p]);
            }

            /* bug possible conflict on getting id
            $count = 0;
            // get the last package id
            $lastID = 0;
            for ($p = 0; $p < count($supID); $p++) {
                if ($supName[$p] != "") {
                    if ($supID[$p] > $lastID) {
                        $lastID = $supID[$p];
                    }
                }
            }

            for ($p = 0; $p < count($supID); $p++) {
                if ($supName[$p] != "") {
                    $curentID = $supID[$p];

                    if ($curentID == "") {
                        $lastID++;
                        $curentID = $lastID;
                        $supID[$p] = $lastID;
                    }
                    if ($curentID == 1 && $p > 0) {
                        $lastID++;
                        $curentID = $lastID;
                        $supID[$p] = $lastID;
                    }
                    if(! in_array($supResource[$p], $choicesRid)) {
                        continue;
                    }
                    //echo "set package (".$curentID." , " . $id_resource ." , " . $packageName[$p]." , ". $packageDuration[$p] . ")<br/>";
                    $modelSups->setCalSupInfo($id_space, $curentID, $supResource[$p], $supName[$p], $supMandatory[$p]);
                    $count++;
                }
            }
            */

            $modelSups->removeUnlistedSupInfos($id_space, $supID);
            $_SESSION["message"] = BookingTranslator::Supplementaries_saved($lang);
            $this->redirect("bookingsupsinfo/".$id_space);
            return;
        }
        // view
        $formHtml = $form->getHtml($lang);
        $this->render(array(
            'id_space' => $id_space,
            "lang" => $lang,
            'formHtml' => $formHtml
        ));
    }

}

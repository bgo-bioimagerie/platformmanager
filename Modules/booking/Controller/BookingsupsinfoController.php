<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkCalSupInfo.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/core/Model/CoreVirtual.php';
require_once 'Modules/booking/Controller/BookingsupsabstractController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingsupsinfoController extends BookingsupsabstractController {

    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        $this->modelSups = new BkCalSupinfo();
        $this->supsType = "supinfo";
        $this->supsTypePlural = "supplementaries";
        $this->invoicable = false;
        $this->mandatoryFields = true;
        $this->formUrl = "bookingsupsinfo";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $form = $this->getSupForm($id_space, BookingTranslator::supplementaries($lang));

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
                if ($supName[$p] != "") {
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
                    $this->modelSups->setCalSupInfo($id_space, $supID[$p], $supResource[$p], $supName[$p], $supMandatory[$p]);
                }
            }

            $this->modelSups->removeUnlistedSupInfos($id_space, $supID);
            $_SESSION['flash'] = BookingTranslator::Supplementaries_saved($lang);
            $_SESSION["flashClass"] = 'success';
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

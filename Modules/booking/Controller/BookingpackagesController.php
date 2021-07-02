<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkPackage.php';
require_once 'Modules/resources/Model/ResourceInfo.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingpackagesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("bookingsettings");
        $_SESSION["openedNav"] = "bookingsettings";
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

        $modelPackages = new BkPackage();
        $packages = $modelPackages->getForSpace($id_space, "id_resource");
        $packagesIds = array();
        $packagesIdsRes = array();
        $packagesNames = array();
        $packagesDuration = array();
        foreach ($packages as $p) {
            $packagesIds[] = $p["id_package"];
            $packagesIdsRes[] = $p["id_resource"];
            $packagesNames[] = $p["name"];
            $packagesDuration[] = $p["duration"];
        }

        $form = new Form($this->request, "packagesForm");
        $form->setTitle(BookingTranslator::Packages($lang));

        $formAdd = new FormAdd($this->request, "packagesAddForm");
        $formAdd->addHidden("id_package", $packagesIds);
        $formAdd->addSelect("id_resources", BookingTranslator::Resource($lang), $choicesR, $choicesRid, $packagesIdsRes);
        $formAdd->addText("names", CoreTranslator::Name($lang), $packagesNames);
        $formAdd->addNumber("durations", BookingTranslator::Duration($lang), $packagesDuration);

        $formAdd->setButtonsNames(CoreTranslator::Add(), CoreTranslator::Delete($lang));
        $form->setFormAdd($formAdd);
        $form->setValidationButton(CoreTranslator::Save($lang), "bookingpackages/".$id_space);
        $form->setButtonsWidth(2, 9);

        if ($form->check()) {
            $packageID = $this->request->getParameterNoException("id_package");
            $packageResource = $this->request->getParameterNoException("id_resources");
            $packageName = $this->request->getParameterNoException("names");
            $packageDuration = $this->request->getParameterNoException("durations");

            //print_r($packageID);

            $count = 0;

            $rem = new ResourceInfo();
            $spaceResources = $rem->getForSpace($id_space);
            $spaceResourcesIDs = [];
            foreach ($spaceResources as $spaceResource) {
                $spaceResourcesIDs[] = $spaceResource['id'];
            }

            // Check resources are in space
            foreach ($packageResource as $id) {
                if(!in_array($id, $spaceResourcesIDs)) {
                    Configuration::getLogger()->error('Unauthorized access to resource', ['resource' => $id]);
                    throw new PfmAuthException('access denied for this resource', 403);
                }
            }

            // If package specified, check it exists in space
            foreach ($packageID as $id) {
                if($id && !in_array($id, $packagesIds)) {
                    Configuration::getLogger()->error('Unauthorized access to resource', ['resource' => $id]);
                    throw new PfmAuthException('access denied for this resource', 403);                }
            }

            // get the last package id
            $lastID = 0;
            for ($p = 0; $p < count($packageID); $p++) {
                if ($packageName[$p] != "") {
                    if ($packageID[$p] > $lastID) {
                        $lastID = $packageID[$p];
                    }
                }
            }

            for ($p = 0; $p < count($packageID); $p++) {
                if ($packageName[$p] != "") {
                    $curentID = $packageID[$p];

                    if ($curentID == "") {
                        $lastID++;
                        $curentID = $lastID;
                        $packageID[$p] = $lastID;
                    }
                    if ($curentID == 1 && $p > 0) {
                        $lastID++;
                        $curentID = $lastID;
                        $packageID[$p] = $lastID;
                    }

                    //echo "set package (".$curentID." , " . $id_resource ." , " . $packageName[$p]." , ". $packageDuration[$p] . ")<br/>";
                    $modelPackages->setPackage($curentID, $packageResource[$p], $packageName[$p], $packageDuration[$p]);
                    $count++;
                }
            }

            // Refresh packages
            $packages = $modelPackages->getForSpace($id_space, "id_resource");
            // If package in db is not listed in provided package list, delete them
            foreach ($packages as $p) {
                if($p['id_package'] && !in_array($p['id_package'], $packageID)) {
                    $modelPackages->deletePackage($p['id']);
                }
            }   
            // $modelPackages->removeUnlistedPackages($packageID);
            $_SESSION["message"] = BookingTranslator::Packages_saved($lang);
            $this->redirect("bookingpackages/".$id_space);
            return;
        }
        // view
        $formHtml = $form->getHtml($lang);
        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            'formHtml' => $formHtml
        ));
    }

}

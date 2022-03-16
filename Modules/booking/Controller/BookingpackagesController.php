<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkPackage.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/core/Model/CoreVirtual.php';
require_once 'Modules/booking/Controller/BookingsettingsController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingpackagesController extends BookingsettingsController {

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
        $formAdd->addLabel(BookingTranslator::Package($lang), $packagesIds);

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
                    throw new PfmAuthException('access denied for this resource', 403);
                }
            }


            $packs = [];
            for ($p = 0; $p < count($packageID); $p++) {
                if ($packageName[$p] != "" && $packageID[$p]) {
                   $packs[$packageName[$p]] = $packageID[$p];
                }
            }

            $couplePackageResourceExists = false;
            for ($p = 0; $p < count($packageID); $p++) {
                if($packageName[$p] == "") {
                    continue;
                }
                if (!$packageID[$p]) {
                    // If package id not set, use from known packages
                    if(isset($packs[$packageName[$p]])) {
                        $packageID[$p] = $packs[$packageName[$p]];
                        if ($this->couplePackageResourceExists($packageID[$p],$packageResource[$p], $id_space)) {
                            $couplePackageResourceExists = [
                                "resource" => $modelResource->get($id_space, $packageResource[$p])['name'],
                                "package" => $packageName[$p]
                            ];
                        }
                    } else {
                        // Or create a new package
                       $cvm = new CoreVirtual();
                       $vid = $cvm->new('package');
                       $packageID[$p] = $vid;
                       $packs[$packageName[$p]] = $vid;
                   }
                }
                $modelPackages->setPackage($id_space, $packageID[$p], $packageResource[$p], $packageName[$p], $packageDuration[$p]);
            }

            // get the last package id
            // @bug, should get from an increment in table, risk of conflict
            /*            
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
                    $modelPackages->setPackage($id_space, $curentID, $packageResource[$p], $packageName[$p], $packageDuration[$p]);
                    $count++;
                }
            }
            */
            //  get all ids from id_packages
            // TODO: to test with deleted resources
            $id_packages = [];
            for ($i=0; $i<count($packageID); $i++) {
                array_push($id_packages, $modelPackages->getByPackageID($id_space, $packageID[$i], $packageResource[$i])['id']);
            }

            // $modelPackages->removeUnlistedPackages($id_space, $packageID);
            $modelPackages->removeUnlistedPackages($id_space, $id_packages, false);
            if ($couplePackageResourceExists) {
                $_SESSION["flash"] = BookingTranslator::Package_resource_exists(
                    $couplePackageResourceExists["package"],
                    $couplePackageResourceExists["resource"],
                    $lang
                );
                $_SESSION["flashClass"] = 'danger';
            } else {
                $_SESSION["flash"] = BookingTranslator::Packages_saved($lang);
                $_SESSION["flashClass"] = 'success';
            }
            
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

    protected function couplePackageResourceExists($id_package, $id_resource, $id_space) {
        $modelPackage = new BkPackage();
        $dbPackages = $modelPackage->getByResource($id_space, $id_resource);
        foreach ($dbPackages as $dbPackage) {
            if ($dbPackage['id_package'] == $id_package) {
                return true;
            }
        }
        return false;
    }

}

<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/clients/Model/ClPricing.php';

require_once 'Modules/invoices/Model/InvoicesTranslator.php';

require_once 'Modules/booking/Model/BkPrice.php';
require_once 'Modules/booking/Model/BkOwnerPrice.php';
require_once 'Modules/booking/Model/BkNightWE.php';
require_once 'Modules/booking/Model/BkPackage.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/booking/Controller/BookingsettingsController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BookingpricesController extends BookingsettingsController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("booking", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelPricing = new ClPricing();
        $modelResource = new ResourceInfo();
        $resources = $modelResource->getBySpace($idSpace);

        // TODO: handle case where $belongings = null; Causes warnings in dev mode
        $belongings = $modelPricing->getAll($idSpace);
        $table = new TableView();

        $modelConfig = new CoreConfig();
        $bookingmenuname = $modelConfig->getParamSpace("bookingmenuname", $idSpace);

        $table->setTitle(BookingTranslator::Prices($lang) . " " . $bookingmenuname, 3);

        $headers = array(
            "resource" => ResourcesTranslator::Resource($lang)
        );
        for ($i = 0 ; $i < count($belongings) ; $i++) {
            $headers[$belongings[$i]["id"]] = $belongings[$i]["name"];
        }

        $prices = array();
        $modelPrice = new BkPrice();
        $modelPNightWe = new BkNightWE();
        $modelPackage = new BkPackage();
        $count = 0;
        $data = array();
        for ($i = 0; $i < count($resources); $i++) {
            $count++;
            // day
            for ($b = 0; $b < count($belongings); $b++) {
                $data[$belongings[$b]["id"]] = $modelPrice->getDayPrice($idSpace, $resources[$i]["id"], $belongings[$b]["id"]);
            }
            $data['id_resource'] = $resources[$i]["id"] . "-day";
            $data['resource'] = $resources[$i]["name"];
            $ress[] = array("id" => $data['id_resource'], "name" => $data['resource']);
            $prices[] = $data;
            // add night we
            $isNight = $belongings ? $modelPNightWe->isNight($idSpace, $belongings[0]["id"]) : null;
            if ($isNight) {
                $count++;
                for ($b = 0; $b < count($belongings); $b++) {
                    $data[$belongings[$b]["id"]] = $modelPrice->getNightPrice($idSpace, $resources[$i]["id"], $belongings[$b]["id"]);
                }
                $data['id_resource'] = $resources[$i]["id"] . "-night";
                $data['resource'] = $resources[$i]["name"] . " " . BookingTranslator::night($lang);
                $ress[] = array("id" => $data['id_resource'], "name" => $data['resource']);
                $prices[] = $data;
            }
            $isWe = $belongings ? $modelPNightWe->isWe($idSpace, $belongings[0]["id"]) : null;
            if ($isWe) {
                $count++;
                for ($b = 0; $b < count($belongings); $b++) {
                    $data[$belongings[$b]["id"]] = $modelPrice->getWePrice($idSpace, $resources[$i]["id"], $belongings[$b]["id"]);
                }
                $data['id_resource'] = $resources[$i]["id"] . "-we";
                $data['resource'] = $resources[$i]["name"] . " " . BookingTranslator::WE($lang);
                $ress[] = array("id" => $data['id_resource'], "name" => $data['resource']);
                $prices[] = $data;
            }

            // add forfaits
            $packages = $modelPackage->getByResource($idSpace, $resources[$i]["id"]);
            foreach ($packages as $package) {
                $count++;
                for ($b = 0; $b < count($belongings); $b++) {
                    $data[$belongings[$b]["id"]] = $modelPrice->getPackagePrice($idSpace, $package["id_package"], $resources[$i]["id"], $belongings[$b]["id"]);
                }
                $data['id_resource'] = $resources[$i]["id"] . "-pk-" . $package["id_package"];
                $data['resource'] = $resources[$i]["name"] . " " . $package["name"];
                $ress[] = array("id" => $data['id_resource'], "name" => $data['resource']);
                $prices[] = $data;
            }
        }

        $table->addLineEditButton('editentry', 'id_resource', true);
        $tableHtml = $table->view($prices, $headers);

        $form = new Form($this->request, "resourcesPricesForm");
        $form->setTitle(BookingTranslator::Prices($lang), 3);
        $form->addHidden("resource_id");
        $form->addText("resource", ResourcesTranslator::resource($lang), false, "", false);
        for ($b = 0 ; $b < count($belongings) ; $b++) {
            $form->addText('bel_'.$belongings[$b]['id'], $belongings[$b]['name'], true, 0);
        }

        $form->setValidationButton(CoreTranslator::Save($lang), "bookingpriceseditquery/".$idSpace);

        return $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "tableHtml" => $tableHtml,
            'formedit' => $form->getHtml($lang),
            'resources' => $ress,
            'belongings' => $belongings,
            'data' => ['prices' => $prices]
        ));
    }

    public function editqueryAction($idSpace)
    {
        $modelBelonging = new ClPricing();
        $modelPrice = new BkPrice();
        $belongings = $modelBelonging->getAll($idSpace);

        $id_resource = $this->request->getParameter('resource_id');

        $residArray = explode("-", $id_resource);


        $resourceModel = new ResourceInfo();
        $res = $resourceModel->get($idSpace, $residArray[0]);
        if (!$res) {
            Configuration::getLogger()->error('Unauthorized access to resource', ['resource' => $id_resource]);
            throw new PfmAuthException('access denied for this resource', 403);
        }

        if ($residArray[1] == "day") {
            foreach ($belongings as $bel) {
                $price = $this->request->getParameter('bel_' . $bel['id']);
                $modelPrice->setPriceDay($idSpace, $residArray[0], $bel["id"], $price);
            }
        } elseif ($residArray[1] == "night") {
            foreach ($belongings as $bel) {
                $price = $this->request->getParameter('bel_' . $bel['id']);
                $modelPrice->setPriceNight($idSpace, $residArray[0], $bel["id"], $price);
            }
        } elseif ($residArray[1] == "we") {
            foreach ($belongings as $bel) {
                $price = $this->request->getParameter('bel_' . $bel['id']);
                $modelPrice->setPriceWe($idSpace, $residArray[0], $bel["id"], $price);
            }
        } elseif ($residArray[1] == "pk") {
            foreach ($belongings as $bel) {
                $price = $this->request->getParameter('bel_' . $bel['id']);
                $modelPrice->setPricePackage($idSpace, $residArray[0], $bel['id'], $residArray[2], $price);
            }
        }

        $this->redirect('bookingprices/' . $idSpace);
    }
}

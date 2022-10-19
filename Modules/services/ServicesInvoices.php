<?php

require_once 'Framework/Routing.php';

class ServicesInvoices extends Routing
{
    private $idSpace;

    public function setSpace($idSpace)
    {
        $this->id_space = $idSpace;
    }

    public function listRoutes()
    {
        $modelCoreConfig = new CoreConfig();
        $servicesuseproject = $modelCoreConfig->getParamSpace("servicesuseproject", $this->id_space);
        if ($servicesuseproject == 1) {
            $this->addRoute("servicesinvoiceproject", "servicesinvoiceproject", "servicesinvoiceproject", "index", array("id_space"), array(""));
        }
        $servicesusecommand = $modelCoreConfig->getParamSpace("servicesusecommand", $this->id_space);
        if ($servicesusecommand == 1) {
            $this->addRoute("servicesinvoiceorder", "servicesinvoiceorder", "servicesinvoiceorder", "index", array("id_space"), array(""));
        }
        $this->addRoute("servicesprices", "servicesprices", "servicesprices", "index", array("id_space"), array(""));
    }
}

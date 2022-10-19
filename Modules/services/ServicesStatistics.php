<?php

require_once 'Framework/Routing.php';

class ServicesStatistics extends Routing
{
    private $id_space;

    public function setSpace($id_space)
    {
        $this->id_space = $id_space;
    }

    public function listRoutes()
    {
        // statistics
        $modelCoreConfig = new CoreConfig();
        $servicesuseproject = $modelCoreConfig->getParamSpace("servicesuseproject", $this->id_space);
        if ($servicesuseproject == 1) {
            $this->addRoute("servicesstatisticsproject", "servicesstatisticsproject", "servicesstatisticsproject", "index", array("id_space"), array(""));
            $this->addRoute("servicesstatisticsprojectsamplesreturn", "servicesstatisticsprojectsamplesreturn", "servicesstatisticsproject", "samplesreturn", array("id_space"), array(""));
            $this->addRoute("servicesstatisticsmailresps", "servicesstatisticsmailresps", "servicesstatisticsproject", "mailresps", array("id_space"), array(""));
        }
        $servicesusecommand = $modelCoreConfig->getParamSpace("servicesusecommand", $this->id_space);
        if ($servicesusecommand == 1) {
            $this->addRoute("servicesstatisticsorder", "servicesstatisticsorder", "servicesstatisticsorder", "index", array("id_space"), array(""));
        }
    }
}

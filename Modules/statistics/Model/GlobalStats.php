<?php

require_once 'Modules/core/Model/CoreFiles.php';
require_once 'Framework/Request.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/services/Controller/ServicesstatisticsprojectController.php';
require_once 'Modules/booking/Model/BkStats.php';

class GlobalStats {

    public const STATS_GLOBAL = 'global';


    public function generateStats($filepath, $dateBegin, $dateEnd, $excludeColorCode, $generateclientstats, $id_space, $lang='en') {

        $c = new CoreSpace();
        $space = $c->getSpace($id_space);
        $controllerServices = new ServicesstatisticsprojectController(new Request([], false), $space);
        $spreadsheet = $controllerServices->getBalance($dateBegin, $dateEnd, $id_space, true);


        $bk = new BkStats();
        $spreadsheet = $bk->getBalance($dateBegin, $dateEnd, $id_space, $excludeColorCode, $generateclientstats, $spreadsheet);
        $spreadsheet->setActiveSheetIndex(1);

        // write excel file
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");

        // record modifications and download file
        $dir = dirname($filepath);
        if(!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        $objWriter->save($filepath);

        Configuration::getLogger()->debug('[stats] generated', ['file' => $filepath]);

    }

}

?>
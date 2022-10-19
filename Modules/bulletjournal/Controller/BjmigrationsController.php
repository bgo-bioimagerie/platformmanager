<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/bulletjournal/Model/BulletjournalTranslator.php';
require_once 'Modules/bulletjournal/Model/BjNote.php';
require_once 'Modules/bulletjournal/Model/BjTask.php';

/**
 * @deprecated
 * @author sprigent
 * Controller for the home page
 */
class BjmigrationsController extends CoresecureController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace, $year, $month)
    {
        $this->checkAuthorizationMenuSpace("bulletjournal", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        if ($month == "" || $month == 0) {
            $month = date('m', time());
        }
        if ($year == "" || $year == 0) {
            $year = date('Y', time());
        }

        $modelTask = new BjTask();
        $notes = $modelTask->openedForMigration($idSpace, $year, $month);

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "month" => $month,
            "year" => $year, "notes" => $notes,
                ), "indexAction");
    }

    public function monthbeforeAction($idSpace, $year, $month)
    {
        if ($month == 1) {
            $year = $year-1;
            $month = 12;
        } else {
            $month = $month -1;
        }

        $this->indexAction($idSpace, $year, $month);
    }

    public function monthafterAction($idSpace, $year, $month)
    {
        if ($month == 12) {
            $year = $year+1;
            $month = 1;
        } else {
            $month = $month + 1;
        }

        $this->indexAction($idSpace, $year, $month);
    }
}

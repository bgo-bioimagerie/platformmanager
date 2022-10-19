<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/statistics/Model/StatisticsTranslator.php';
require_once 'Modules/statistics/Controller/StatisticsController.php';
require_once 'Modules/core/Model/CoreFiles.php';
/**
 *
 * @author sprigent
 * Controller for the home page
 */
class StatisticslistController extends StatisticsController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("statistics", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $c = new CoreFiles();
        $cs = new CoreSpace();
        $role = $cs->getSpaceMenusRole($idSpace, 'statistics');
        $statFiles = $c->getByModule($idSpace, 'statistics', $role);

        $table = new TableView();


        $table->setTitle(StatisticsTranslator::statistics($lang), 3);

        $headers = array(
            "created_at" => CoreTranslator::Date($lang),
            "name" => CoreTranslator::Name($lang),
            "login" => CoreTranslator::User($lang),
            "status" => CoreTranslator::Status($lang),
            "msg" => ""
        );
        foreach ($statFiles as $i => $stat) {
            switch ($stat['status']) {
                case CoreFiles::$ERROR:
                    $statFiles[$i]['status'] = 'error';
                    break;
                case CoreFiles::$IN_PROGRESS:
                    $statFiles[$i]['status'] = 'in progress';
                    break;
                case CoreFiles::$PENDING:
                    $statFiles[$i]['status'] = 'pending';
                    break;
                case CoreFiles::$READY:
                    $statFiles[$i]['status'] = 'done';
                    break;
                default:
                    $statFiles[$i]['status'] = 'done';
                    break;
            }
            $statFiles[$i]['url'] = '';
            if ($statFiles[$i]['status'] == 'done') {
                $statFiles[$i]['url'] = "corefiles/$idSpace/".$stat['id'];
            }
        }

        $table->addDownloadButton('url');
        $tableView = $table->view($statFiles, $headers);

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "stats" => $tableView));
    }
}

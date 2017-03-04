<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/statistics/Model/StatisticsTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class StatisticsController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("statistics");
    }

    public function navbar($id_space) {

        $html = "";

        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $configModel = new CoreConfig();
        $menus = $modelSpace->getAllSpaceMenusModules($id_space);
        $urls = array();
        $urlss = array();
        $count = -1;
        foreach ($menus as $menu) {
            
            $module = $menu["module"];
            $rootingFile = "Modules/" . $module . "/" . ucfirst($module) . "Statistics.php";
            //echo "rooting file = " . $rootingFile . "<br/>";
            if (file_exists($rootingFile)) {
                $count++;
                //echo $rootingFile . " exists <br/>";
                require_once $rootingFile;
                $className = ucfirst($module) . "Statistics";
                $classTranslator = ucfirst($module) . "Translator";
                require_once 'Modules/' . $module . "/Model/" . $classTranslator . ".php";
                $translator = new $classTranslator();
                $model = new $className();
                $model->setSpace($id_space);
                $model->listRouts();

                if ($model->count() > 0) {
                    //echo "module = " . $module . "<br/>";
                    $donfigTitle = $configModel->getParamSpace($module . "menuname", $id_space);
                    //echo "donfigTitle = " . $donfigTitle . "<br/>";
                    if ($donfigTitle != "") {
                        $txt = $donfigTitle;
                    } else {
                        $txt = $module;
                    }

                    if ($count>0){
                        $html .= '<br/>';
                    }
                    $html .= '<li>';
                    $html .= '<p id="separatorp">' . $txt . '</p>';
                    $html .= '</li>';

                    for ($i = 0; $i < $model->count(); $i++) {
                        $url = $model->getUrl($i);
                        $txt = $translator->$url($lang);

                        $html .= "<li>";
                        $html .= '<div class="inline pm-inline-div">';
                        $html .= '<a href="' . $url . "/" . $id_space . '">' . $txt . '</a>';
                        $html .= '</div>';
                        $html .= '</li>';
                    }
                }
            }
        }

        return $html;
    }

}

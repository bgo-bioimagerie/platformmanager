<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/statistics/Model/StatisticsTranslator.php';
require_once 'Modules/invoices/Model/InvoicesTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class StatisticsController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("statistics");
    }

    public function navbar($id_space) {

        $lang = $this->getLanguage();

        $html  = '<div class="col-xs-12" style="border: none; margin-top: 7px; padding-right: 0px; padding-left: 0px;">';
        $html .= '<div class="col-xs-12" style="height: 50px; padding-top: 15px; background-color:{{bgcolor}}; border-bottom: 1px solid #fff;">';
        $html .= '<a  style="background-color:{{bgcolor}}; color: #fff;" href=""> {{title}}'; 
        $html .= '    <span style="color: #fff; font-size:16px; float:right;" class=" hidden-xs showopacity glyphicon {{glyphicon}}"></span>';
        $html .= '</a>';
        $html .= '</div>';

        $html .= '<div class="col-xs-12 pm-inline-div" style="background-color:{{bgcolor}};">';
        $html .= '<a id="menu-button" href="statisticsglobal/' . $id_space . '">' . StatisticsTranslator::StatisticsGlobal($lang) . '</a>';
        $html .= '</div>';
        
        $modelSpace = new CoreSpace();
        $configModel = new CoreConfig();
        $menus = $modelSpace->getDistinctSpaceMenusModules($id_space);
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

                    if ($count > 0) {
                        $html .= '<br/>';
                    }
                    
                    $html .= '<div class="col-xs-12 pm-inline-div" style="background-color:{{bgcolor}};">';
                        $html .= '<br/>';
                        $html .= '</div>';
                    
                    
                    for ($i = 0; $i < $model->count(); $i++) {
                        $url = $model->getUrl($i);
                        $txt = $translator->$url($lang);

                        $html .= '<div class="col-xs-12 pm-inline-div" style="background-color:{{bgcolor}};">';
                        $html .= '<a id="menu-button" href="' . $url . "/" . $id_space . '">' . $txt . '</a>';
                        $html .= '</div>';
        
                    }
                }
            }
        }
        
        $html.= "</div>";
        
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("statistics", $id_space);
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', InvoicesTranslator::invoices($lang), $html);

        return $html;
    }

}

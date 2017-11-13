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
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("statistics");
    }

    public function navbar($id_space) {

        $lang = $this->getLanguage();

        $html = "<nav class=\"navbar navbar-default sidebar\" style=\"border: 1px solid #f1f1f1;\" role=\"navigation\">";
        $html .= "<div class=\"container-fluid\">";
        $html .= "<div class=\"navbar-header\" style=\"background-color: #e1e1e1;\">";
        $html .= "    <button type=\"button\" class=\"navbar-toggle\" data-toggle=\"collapse\" data-target=\"#bs-sidebar-navbar-collapse-1\">";
        $html .= "        <span class=\"sr-only\">Toggle navigation</span>";
        $html .= "        <span class=\"icon-bar\"></span>";
        $html .= "        <span class=\"icon-bar\"></span>";
        $html .= "        <span class=\"icon-bar\"></span>";
        $html .= "    </button>      ";
        $html .= "</div>";
        $html .= "<div class=\"collapse navbar-collapse\"  id=\"bs-sidebar-navbar-collapse-1\">";
        $html .= "    <ul class=\"nav navbar-nav\" >";
        $html .= "        <li>";
        $html .= "           <a style=\"background-color:{{bgcolor}}; color: #fff; margin-left: -14px;\" href=\"\"> {{title}} <span style=\"font-size:16px;\" class=\"pull-right hidden-xs showopacity glyphicon {{glyphicon}}\"></span></a>";
        $html .= "        </li>";
        $html .= "        <ul class=\"pm-nav-li\">";

        $html .= '<li>';
        $html .= '<div class="inline pm-inline-div">';
        $html .= '<a href="statisticsglobal/' . $id_space . '">' . StatisticsTranslator::StatisticsGlobal($lang) . '</a>';
        $html .= '</div></li><br/>';

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
        
        
                $html.= "</ul>";
        $html.=  "   </ul>";
        $html.= "</div>";
        $html.= "</div>";
        $html.= "</nav>";
        
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("statistics", $id_space);
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', InvoicesTranslator::invoices($lang), $html);

        return $html;
    }

}

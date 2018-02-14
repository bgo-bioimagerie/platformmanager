<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';
require_once 'Modules/ecosystem/Model/EcUnit.php';
require_once 'Modules/ecosystem/Model/EcUser.php';

require_once 'Modules/invoices/Model/InInvoice.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class InvoicesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("invoices");
    }

    public function navbar($id_space) {

        $lang = $this->getLanguage();
        
        $html = '<nav class="navbar navbar-default sidebar" role="navigation" style="border: none;">';
        $html .= '<div class="container">';
        $html .= '<div class="navbar-header" style="background-color: #e7ecf0;">';
        $html .= '    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-sidebar-navbar-collapse-1">';
        $html .= '       <span class="sr-only">Toggle navigation</span>';
        $html .= '       <span class="icon-bar"></span>';
        $html .= '       <span class="icon-bar"></span>';
        $html .= '       <span class="icon-bar"></span>';
        $html .= '   </button>  ';    
        $html .= ' </div>';
        $html .= ' <div class="collapse navbar-collapse" style="border: none;">';
        $html .= '   <ul class="nav navbar-nav" style="width: 25%" id="bs-sidebar-navbar-collapse-1" >';
        $html .= '       <li style="width: 100%">';
        $html .= '           <a  style="background-color:{{bgcolor}}; color: #fff;" href=""> {{title}} ';
        $html .= '          <span style="color: #fff; font-size:16px; float:right;" class=" hidden-xs showopacity glyphicon {{glyphicon}}"></span>';
        $html .= '           </a>';
        $html .= '       </li>';
        $html .= '       <ul class="pm-nav-li">';
        
        
        $html .= "<li>";
        $html .= '<div class="inline pm-inline-div">';
        $html .= '<a href="invoicestosend/' . $id_space . '">' . InvoicesTranslator::To_Send_invoices($lang) . '</a>';
        $html .= '</div>';
        $html .= '</li>';
        $html .= "<li>";
        $html .= '<div class="inline pm-inline-div">';
        $html .= '<a href="invoicessent/' . $id_space . '">' . InvoicesTranslator::Sent_invoices($lang) . '</a>';
        $html .= '</div>';
        $html .= '</li>';
        
        $html .= "<li>";
        $html .= '<div class="inline pm-inline-div">';
        $html .= '<a href="invoicesvisas/' . $id_space . '">' . InvoicesTranslator::Visas($lang) . '</a>';
        $html .= '<a href="invoicesvisaedit/' . $id_space . '/0"> + </a>';
        $html .= '</div>';
        $html .= '</li><br/>';
        
        $html .= "<li>";
        $html .= '<div class="inline pm-inline-div">';
        $html .= '<a href="invoiceglobal/' . $id_space . '">' . InvoicesTranslator::NewInvoice($lang) . '</a>';
        $html .= '</div>';
        $html .= '</li><br/>';

        
        $modelSpace = new CoreSpace();
        $configModel = new CoreConfig();
        $menus = $modelSpace->getDistinctSpaceMenusModules($id_space);
        
        //print_r($menus);
        
        $count = -1;
        foreach ($menus as $menu) {
            //echo "curent menu " . $menu["module"] . "<br/>";
            $module = $menu["module"];
            $rootingFile = "Modules/" . $module . "/" . ucfirst($module) . "Invoices.php";
            //echo "rooting file = " . $rootingFile . "<br/>";
            if (file_exists($rootingFile)) {

                $count++;
                //echo $rootingFile . " exists <br/>";
                require_once $rootingFile;
                $className = ucfirst($module) . "Invoices";
                $classTranslator = ucfirst($module) . "Translator";
                require_once 'Modules/' . $module . "/Model/" . $classTranslator . ".php";
                $translator = new $classTranslator();
                $model = new $className();
                $model->setSpace($id_space);
                $model->listRouts();
                if ($model->count() > 0) {
                    $donfigTitle = $configModel->getParamSpace($module . "menuname", $id_space);
                    //echo "donfigTitle = " . $donfigTitle . "<br/>";
                    if ($donfigTitle != "") {
                        $txt = $donfigTitle;
                    } else {
                        $txt = $module;
                    }

                    if ($count > 0) {
                        $html .= "<br/>";
                    }
                    $html .= "<li>";
                    $html .= $txt;
                    $html .= "<li/>";
                }
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
        
        $html.= "</ul>";
        $html.=  "   </ul>";
        $html.= "</div>";
        $html.= "</div>";
        $html.= "</nav>";
        
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("invoices", $id_space);
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', InvoicesTranslator::invoices($lang), $html);
        
        return $html;
    }
}

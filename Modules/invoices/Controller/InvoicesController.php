<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/invoices/Model/InvoicesTranslator.php';

require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class InvoicesController extends CoresecureController {

    public function sideMenu() {
        $id_space = $this->args['id_space'];
        return $this->navbar($id_space);
    }

    public function navbar($id_space) {

        $lang = $this->getLanguage();
        
        $html  = '<div class="col-12" style="border: none; margin-top: 7px; padding-right: 0px; padding-left: 0px;">';
        $html .= '<div class="col-12" style="height: 50px; padding-top: 15px; background-color:{{bgcolor}}; border-bottom: 1px solid #fff;">';
        $html .= '<a  style="background-color:{{bgcolor}}; color: {{color}};" href="invoices/'.$id_space.'"> {{title}}'; 
        $html .= '    <span style="color: {{color}}; font-size:16px; float:right;" class=" hidden-xs showopacity glyphicon {{glyphicon}}"></span>';
        $html .= '</a>';
        $html .= '</div>';

        $html .= '<div class="col-12 pm-inline-div" style="background-color:{{bgcolor}};">';
        $html .= '<a style="color:{{color}}" id="menu-button" href="invoicestosend/' . $id_space . '">' . InvoicesTranslator::To_Send_invoices($lang) . '</a>';
        $html .= '</div>';
        
        $html .= '<div class="col-12 pm-inline-div" style="background-color:{{bgcolor}};">';
        $html .= '<a style="color:{{color}}" id="menu-button" href="invoicessent/' . $id_space . '">' . InvoicesTranslator::Sent_invoices($lang) . '</a>';
        $html .= '</div>';
        
        $html .= '<div class="col-12 pm-inline-div" style="background-color:{{bgcolor}};">';
        $html .= '<a style="color:{{color}}" id="menu-button" href="invoicesvisas/' . $id_space . '">' . InvoicesTranslator::Visas($lang) . '</a>';
        $html .= '<a style="color:{{color}}" id="menu-button" href="invoicesvisaedit/' . $id_space . '/0"> + </a>';
        $html .= '</div>';
        
        
        $html .= '<div class="col-12 pm-inline-div" style="background-color:{{bgcolor}};color:{{color}}">';
        $html .= '<br/>';
        $html .= '</div>';
        
        $html .= '<div class="col-12 pm-inline-div" style="background-color:{{bgcolor}};color:{{color}}">';
        $html .= '<a style="color:{{color}}" href="invoiceglobal/' . $id_space . '">' . InvoicesTranslator::NewInvoice($lang) . '</a>';
        $html .= '</div>';
        
        $html .= '<div class="col-12 pm-inline-div" style="background-color:{{bgcolor}};color:{{color}}">';
        $html .= '<br/>';
        $html .= '</div>';
        

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
                $model->listRoutes();
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
                    
                    $html .= '<div class="col-12 pm-inline-div" style="background-color:{{bgcolor}};color:{{color}}">';
                    $html .= '<br/>';
                    $html .= $txt;
                    $html .= '</div>';
                    
                }
                for ($i = 0; $i < $model->count(); $i++) {
                    $url = $model->getUrl($i);
                    $txt = $translator->$url($lang);

                    $html .= '<div class="col-12 pm-inline-div" style="background-color:{{bgcolor}};color:{{color}}">';
                    $html .= '<a style="color:{{color}}" href="' . $url . "/" . $id_space . '">' . $txt . '</a>';
                    $html .= '</div>';
                }
            }
        }
        
        $html.= "</div>";
        
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("invoices", $id_space);
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{color}}', $menuInfo['txtcolor'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', InvoicesTranslator::invoices($lang), $html);
        
        return $html;
    }

}

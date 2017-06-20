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
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("invoices");
    }

    public function navbar($id_space) {

        $lang = $this->getLanguage();
        $html = "<li>";
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
        return $html;
    }
}

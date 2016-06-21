<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/dev/Model/DevTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class DevController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->checkAuthorizationMenu("dev");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction() {
        $lang = $this->getLanguage();

        $form = new Form($this->request, "devForm");
        $form->setTitle(DevTranslator::GenerateModule($lang));
        $form->addText("name", CoreTranslator::Name($lang), true, $this->request->getParameterNoException("name"));
        $form->setColumnsWidth(3, 6);
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(DevTranslator::Generate($lang), "dev");

        if ($form->check()) {
            $moduleName = $form->getParameter("name");
            $this->generateModule(strtolower($moduleName), "data/dev/template/", "Modules/");
            $cache = new FCache();
            $cache->load();
            $_SESSION["message"] = DevTranslator::TheModuleHasBeenGenerated($lang);
        }

        $this->render(array("lang" => $lang, "htmlForm" => $form->getHtml($lang)));
    }

    static public function generateModule($moduleName, $source, $dest) {
        // recursive function to copy
        // all subdirectories and contents:
        if (is_dir($source)) {
            $dir_handle = opendir($source);
            $sourcefolder1 = basename($source);
            $sourcefolder2 = str_replace("template", strtolower($moduleName), $sourcefolder1);
            $sourcefolder = str_replace("Template", ucfirst(strtolower($moduleName)), $sourcefolder2);
            
            //echo "source = " . $source . "<br/>";
            //echo "dest = " . $dest . "<br/>";
            //echo "create dir: " . $dest . "/" . $sourcefolder . "<br/>";
            mkdir($dest . "/" . $sourcefolder);
            while ($file = readdir($dir_handle)) {
                if ($file != "." && $file != "..") {
                    if (is_dir($source . "/" . $file)) {
                        //echo "<br/> call generate module on dir " . $dest . "/" . $sourcefolder . "<br/>";
                        self::generateModule($moduleName, $source . "/" . $file, $dest . "/" . $sourcefolder);
                    } else {
                        if ($file[0] != "."){ // ignore hidden files
                            $fileDest1 = str_replace("template", strtolower($moduleName), $file);
                            $fileDest = str_replace("Template", ucfirst(strtolower($moduleName)), $fileDest1);
                            //echo "create file dir " . $dest . "/" . $fileDest . "<br/>";
                            copy($source . "/" . $file, $dest . "/" . $sourcefolder . "/" . $fileDest);
                            self::fileReplaceModule($moduleName, $dest . "/" . $sourcefolder . "/" . $fileDest);
                        }
                    }
                }
            }
            closedir($dir_handle);
        }
    }
    
    static protected function fileReplaceModule($moduleName, $file){
        $str=file_get_contents($file);
        $str1=str_replace("template", strtolower($moduleName),$str);
        $str2=str_replace("Template", ucfirst(strtolower($moduleName)),$str1);
        file_put_contents($file, $str2);
    }

}

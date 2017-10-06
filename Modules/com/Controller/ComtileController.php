<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/com/Model/ComTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ComtileController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("com");
    }

    public function editAction($id_space){
        
        $modelParam = new CoreConfig();
        $message = $modelParam->getParamSpace("tilemessage", $id_space);
        
        $lang = $this->getLanguage();
        $form = new Form($this->request, "comtileeditform");
        $form->setTitle(ComTranslator::Tilemessage($lang));
        $form->addTextArea("message", "" , false, $message, true);
        $form->setColumnsWidth(0, 12);
        $form->setValidationButton(CoreTranslator::Ok($lang), "comtileedit/".$id_space);
        $form->setButtonsWidth(1, 11);
        
        if($form->check()){
            $modelParam->setParam("tilemessage", $this->request->getParameter("message", false), $id_space);
            $this->redirect('comtileedit/'.$id_space);
        }
        
        $this->render(array("id_space" => $id_space, "formHtml" => $form->getHtml($lang)));
    }
    
    public function indexAction($id_space){
        
        $modelParam = new CoreConfig();
        $message = $modelParam->getParamSpace("tilemessage", $id_space);
        
        return $this->generateComView(array("message" => $message));
    }

    private function generateComView($data) {
        $file = 'Modules/com/View/Comtile/indexAction.php';
        if (file_exists($file)) {
            extract($data);

            ob_start();

            require $file;

            return ob_get_clean();
        } else {
            throw new Exception("unable to find the file: '$file' ");
        }
    }
}

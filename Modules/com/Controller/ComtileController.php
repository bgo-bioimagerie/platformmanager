<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/com/Model/ComTranslator.php';
require_once 'Modules/com/Controller/ComController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ComtileController extends ComController {

    public function editAction($id_space){
        $this->checkAuthorizationMenuSpace("com", $id_space, $_SESSION["id_user"]);
        if($this->role < CoreSpace::$ADMIN) {
            throw new PfmAuthException('admins only');
        }
        $modelParam = new CoreConfig();
        $message_public = $modelParam->getParamSpace("tilemessage", $id_space);
        $message_private = $modelParam->getParamSpace("private_tilemessage", $id_space);
        
        $lang = $this->getLanguage();
        $form = new Form($this->request, "comtileeditform");
        $form->setTitle(ComTranslator::Tilemessage($lang));
        $form->addTextArea("message_public", ComTranslator::PublicTilemessage($lang) , false, $message_public, true);
        $form->addTextArea("message_private", ComTranslator::PrivateTilemessage($lang) , false, $message_private, true);
        $form->setColumnsWidth(0, 12);
        $form->setValidationButton(CoreTranslator::Ok($lang), "comtileedit/".$id_space);
        
        if($form->check()) {
            $modelParam->setParam("tilemessage", $this->request->getParameter("message_public", false), $id_space);
            $modelParam->setParam("private_tilemessage", $this->request->getParameter("message_private", false), $id_space);
            $this->redirect('comtileedit/'.$id_space);
        }
        
        $this->render(array("id_space" => $id_space, "formHtml" => $form->getHtml($lang)));
    }
    
    public function indexAction($id_space){
        
        $modelParam = new CoreConfig();
        $spaceModel = new CoreSpace();
        $role = $spaceModel->getUserSpaceRole($id_space, $_SESSION['id_user']);
        $message_public = $modelParam->getParamSpace("tilemessage", $id_space);
        $message_private = $modelParam->getParamSpace("private_tilemessage", $id_space);

        $messages = array("message_public" => $message_public);

        if($role >= CoreSpace::$USER) {
            $messages["message_private"] = $message_private;
        }
        
        return $this->generateComView(array("messages" => $messages));
    }

    private function generateComView($data) {
        $file = 'Modules/com/View/Comtile/indexAction.php';
        if (file_exists($file)) {
            extract($data);

            ob_start();

            require $file;

            return ob_get_clean();
        } else {
            throw new PfmFileException("unable to find the file: '$file' ", 404);
        }
    }
}

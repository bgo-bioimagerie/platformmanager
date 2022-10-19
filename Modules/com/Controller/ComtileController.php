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
class ComtileController extends ComController
{
    public function editAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("com", $idSpace, $_SESSION["id_user"]);
        if ($this->role < CoreSpace::$ADMIN) {
            throw new PfmAuthException('admins only');
        }
        $modelParam = new CoreConfig();
        $message_public = $modelParam->getParamSpace("tilemessage", $idSpace);
        $message_private = $modelParam->getParamSpace("private_tilemessage", $idSpace);

        $lang = $this->getLanguage();
        $form = new Form($this->request, "comtileeditform");
        $form->setTitle(ComTranslator::Tilemessage($lang));
        $form->addTextArea("message_public", ComTranslator::PublicTilemessage($lang), false, $message_public, true);
        $form->addTextArea("message_private", ComTranslator::PrivateTilemessage($lang), false, $message_private, true);
        $form->setColumnsWidth(0, 12);
        $form->setValidationButton(CoreTranslator::Ok($lang), "comtileedit/".$idSpace);

        if ($form->check()) {
            $modelParam->setParam("tilemessage", $this->request->getParameter("message_public", false), $idSpace);
            $modelParam->setParam("private_tilemessage", $this->request->getParameter("message_private", false), $idSpace);
            $this->redirect('comtileedit/'.$idSpace);
        }

        $this->render(array("id_space" => $idSpace, "formHtml" => $form->getHtml($lang)));
    }

    public function indexAction($idSpace)
    {
        $modelParam = new CoreConfig();
        $message_public = $modelParam->getParamSpace("tilemessage", $idSpace);
        $message_private = $modelParam->getParamSpace("private_tilemessage", $idSpace);

        $messages = array("message_public" => $message_public);

        if ($this->role >= CoreSpace::$USER) {
            $messages["message_private"] = $message_private;
        }

        return $this->generateComView(array("messages" => $messages));
    }

    private function generateComView($data)
    {
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

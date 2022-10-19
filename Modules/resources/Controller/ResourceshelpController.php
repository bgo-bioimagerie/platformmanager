<?php

require_once 'Framework/Controller.php';

require_once 'Modules/core/Controller/CoresecureController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class ResourceshelpController extends CoresecureController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space)
    {
        $lang = $this->getLanguage();

        $this->render(array("id_space" => $id_space, "lang" => $lang));
    }
}

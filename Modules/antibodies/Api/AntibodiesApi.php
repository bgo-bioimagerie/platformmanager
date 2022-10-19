<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/Tissus.php';
require_once 'Modules/antibodies/Model/AcOwner.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class AntibodiesApi extends CoresecureController
{
    /**
     * Constructor
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("bulletjournal");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::tissusAction()
     */
    public function tissusAction($idSpace, $id_tissus)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);

        $modelTissus = new Tissus();
        $data = $modelTissus->getTissusById($idSpace, $id_tissus);

        echo json_encode($data);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::ownerAction()
     */
    public function ownerAction($idSpace, $id_owner)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);

        $model = new AcOwner();
        $data = $model->get($idSpace, $id_owner);

        echo json_encode($data);
    }
}

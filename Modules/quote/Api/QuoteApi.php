<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/quote/Model/QuoteItem.php';



/**
 *
 * @author sprigent
 * Controller for the home page
 */
class QuoteApi extends CoresecureController
{
    /**
     * Constructor
     */
    public function __construct(Request $request)
    {
        parent::__construct($request);
    }

    public function getitemAction($id_space, $id)
    {
        $model = new QuoteItem();
        $data = $model->get($id_space, $id);

        echo json_encode($data);
    }
}

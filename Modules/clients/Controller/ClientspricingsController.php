<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Constants.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/clients/Model/ClPricing.php';
require_once 'Modules/clients/Controller/ClientsController.php';

/**
 *
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class ClientspricingsController extends ClientsController
{
    /**
     * User model object
     */
    private $pricingModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->pricingModel = new ClPricing();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     *
     * Page showing a table containing all the providers in the database
     */
    public function indexAction($idSpace)
    {
        // security
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // Query to the database
        $belongingsArray = $this->pricingModel->getAll($idSpace);
        $pricings = $belongingsArray;
        for ($i = 0; $i < count($belongingsArray); $i++) {
            if ($belongingsArray[$i]["type"] == 1) {
                $belongingsArray[$i]["type"] = CoreTranslator::Academic($lang);
            } else {
                $belongingsArray[$i]["type"] = CoreTranslator::Company($lang);
            }
        }

        $table = new TableView();
        $table->addLineEditButton("clpricingedit/" . $idSpace);
        $table->addDeleteButton("clpricingdelete/" . $idSpace);
        $table->setColorIndexes(array("color" => "color", "txtcolor" => "txtcolor"));
        $tableHtml = $table->view(
            $belongingsArray,
            array("name" => CoreTranslator::Name($lang),
            "color" => CoreTranslator::color($lang),
            "txtcolor" => CoreTranslator::text_color($lang),
            "type" => CoreTranslator::type($lang),
            "id" => "ID"
        )
        );

        // render the View
        return $this->render(array(
            'id_space' => $idSpace,
            'lang' => $lang,
            'tableHtml' => $tableHtml,
            'data' => ['pricings' => $pricings]
        ));
    }

    /**
     * Edit a provider form
     */
    public function editAction($idSpace, $id)
    {
        // security
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        //lang
        $lang = $this->getLanguage();

        // default empy provider
        if (!$id) {
            $pricing = array(
                "id" => 0,
                "name" => "",
                "color" => Constants::COLOR_WHITE,
                "txtcolor" => Constants::COLOR_BLACK,
                "display_order" => 0,
                "type" => 0
            );
        } else {
            $pricing = $this->pricingModel->get($idSpace, $id);
        }



        // form
        // build the form
        $form = new Form($this->request, "pricing/edit");
        $form->setTitle(ClientsTranslator::Edit_Pricing($lang), 3);
        $form->addHidden("id", $pricing["id"]);
        $form->addText("name", CoreTranslator::Name($lang), true, $pricing["name"]);
        $form->addColor("color", CoreTranslator::color($lang), false, $pricing["color"]);
        $form->addColor("txtcolor", CoreTranslator::text_color($lang), false, $pricing["txtcolor"]);
        $form->addNumber("display_order", CoreTranslator::Display_order($lang), false, $pricing["display_order"]);

        $choices = array(CoreTranslator::Academic($lang), CoreTranslator::Company($lang));
        $choicesid = array(1, 2);
        $form->addSelect("type", CoreTranslator::type($lang), $choices, $choicesid, $pricing["type"]);

        $todo = $this->request->getParameterNoException('redirect');
        $validationUrl = "clpricingedit/".$idSpace."/".$id;
        if ($todo) {
            $validationUrl .= "?redirect=todo";
        }

        $form->setValidationButton(CoreTranslator::Ok($lang), $validationUrl);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "clpricings/" . $idSpace);


        // Check if the form has been validated
        if ($form->check()) {
            // run the database query
            $newId = $this->pricingModel->set(
                $form->getParameter("id"),
                $idSpace,
                $form->getParameter("name"),
                $form->getParameter("color"),
                $form->getParameter("type"),
                $form->getParameter("display_order"),
                $form->getParameter("txtcolor"),
            );

            $_SESSION["flash"] = ClientsTranslator::Data_has_been_saved($lang);
            $_SESSION["flashClass"] = "success";

            if ($todo) {
                return $this->redirect("spaceadminedit/" . $idSpace, ["showTodo" => true]);
            } else {
                // after the provider is saved we redirect to the providers list page
                return $this->redirect("clpricingedit/" . $idSpace . "/" . $newId, [], ['pricing' => ['id' => $newId]]);
            }
        } else {
            // set the view
            $formHtml = $form->getHtml($lang);
            // render the view
            return $this->render(array(
                'id_space' => $idSpace,
                'lang' => $lang,
                'formHtml' => $formHtml,
                'data' => ['pricing' => $pricing]
            ));
        }
    }

    /**
     * Remove a provider
     */
    public function deleteAction($idSpace, $id)
    {
        // security
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);

        if ($this->pricingModel->hasClients($idSpace, $id)) {
            throw new PfmParamException("Pricing used by clients");
        }

        // query to delete the provider
        $this->pricingModel->delete($idSpace, $id);

        // after the provider is deleted we redirect to the providers list page
        $this->redirect("clpricings/" . $idSpace);
    }

    public function getClientPricingAction($idSpace, $id_client)
    {
        $this->checkAuthorizationMenuSpace("clients", $idSpace, $_SESSION["id_user"]);
        $modelClientPricing = new ClPricing();
        $pricingName = $modelClientPricing->getPricingByClient($idSpace, $id_client)[0]['name'];
        return $this->render(['data' => ['elements' => $pricingName]]);
    }
}

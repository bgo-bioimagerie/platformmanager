<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/rating/Model/RatingTranslator.php';
/**
 * Controller for the rating config page
 */
class RatingconfigController extends CoresecureController
{
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if (!$plan->hasFlag(CorePlan::FLAGS_SATISFACTION)) {
            throw new PfmAuthException('Sorry, space does not have this feature plan');
        }
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();


        // maintenance form
        $formMenusactivation = $this->menusactivationForm($idSpace, 'rating', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($idSpace, 'rating', 'star');
            $this->redirect("ratingconfig/".$idSpace);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang));

        $this->render(array("id_space" => $idSpace, "forms" => $forms, "lang" => $lang));
    }
}

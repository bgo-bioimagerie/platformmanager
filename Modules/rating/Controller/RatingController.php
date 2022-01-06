<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/rating/Model/RatingTranslator.php';
require_once 'Modules/rating/Model/Rating.php';
require_once 'Modules/core/Model/CoreSpace.php';

/**
 * 
 * Controller for the rating page
 */
class RatingController extends CoresecureController {

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if(!$plan->hasFlag(CorePlan::FLAGS_SATISFACTION)) {
            throw new PfmAuthException('Sorry, space does not have this feature plan');
        }
        $this->checkAuthorizationMenuSpace("rating", $id_space, $_SESSION["id_user"]);
        $r = new Rating();
        $stats = $r->stat($id_space);
        foreach($stats as $i => $stat){
            $stats[$i]['rate'] = round($stat['rate']);
        }
        return $this->render(['data' => ['stats' => $stats]]);
    }

    public function rateAction($id_space, $module, $resource) {
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if(!$plan->hasFlag(CorePlan::FLAGS_SATISFACTION)) {
            throw new PfmAuthException('Sorry, space does not have this feature plan');
        }
        $userSpaceStatus = $this->getUserSpaceStatus($id_space, $_SESSION["id_user"]);
        if($userSpaceStatus != CoreSpace::$USER) {
            throw new PfmAuthException("only user space member can evaluate!");
        }
        $r = new Rating();
        $rate = intval($this->request->getParameter("rate"));
        $comment = $this->request->getParameterNoException('comment') ?? '';
        $resourcename = $this->request->getParameterNoException('resourcename') ?? $resource;
        $r->set($id_space, $_SESSION['id_user'], $module, $resource, $resourcename, $rate, $comment);
        return $this->render(['data' => ['rate' => $rate]]);
    }
}
?>
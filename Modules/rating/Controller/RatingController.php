<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/rating/Model/RatingTranslator.php';
require_once 'Modules/rating/Model/Rating.php';
require_once 'Modules/core/Model/CoreSpace.php';

require_once 'Modules/booking/Model/BkCalendarEntry.php';

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

    public function ratingsAction($id_space, $module, $resource) {
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if(!$plan->hasFlag(CorePlan::FLAGS_SATISFACTION)) {
            throw new PfmAuthException('Sorry, space does not have this feature plan');
        }
        $this->checkAuthorizationMenuSpace("rating", $id_space, $_SESSION["id_user"]);
        $r = new Rating();
        $ratings = $r->get($id_space, $module, $resource);
        
        return $this->render(['data' => ['ratings' => $ratings]]);
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
        $resourceName = '';
        if($module == 'booking') {
            $b = new BkCalendarEntry();
            $booking = $b->getEntry($id_space, $resource);
            if(!$booking) {
                throw new PfmParamException('resource not found', 404);
            }
            if($booking['recipient_id'] != $_SESSION['id_user']) {
                throw new PfmAuthException('forbiden, not one of your resources', 403);
            }
            $r = new ResourceInfo();
            $resourceInfo = $r->get($id_space, $booking['resource_id']);
            if(!$resourceInfo) {
                throw new PfmParamException('related resource not found', 404);
            }
            $resourceName = $resourceInfo['name'];
        } else {
            throw new PfmParamException('invalid module');
        }
        $rate = [
            'rate' => 0,
            'module' => $module,
            'resource' => $resource,
            'resourcename' => $resourceName,
            'id_user' => $_SESSION['id_user'],
            'anon' => 1
        ];
        $r = new Rating();
        $evaluated = $r->evaluated($id_space, $module, $resource, $_SESSION['id_user']);
        if($evaluated) {
            throw new PfmAuthException('resource already evaluated');
        }
        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $rateval = intval($this->request->getParameter("rate"));
            $comment = $this->request->getParameterNoException('comment') ?? '';
            $anon = $this->request->getParameterNoException('anon') ?? 1;
            $r->set($id_space, $_SESSION['id_user'], $module, $resource, $resourceName, $rateval, $comment, $anon);
            $rate = [
                'rate' => $rateval,
                'comment' => $comment,
                'resource' => $resource,
                'module' => $module,
                'resourcename' => $resourceName,
                'id_user' => $_SESSION['id_user'],
                'anon' => $anon
            ];

        }
        return $this->render(['data' => ['rate' => $rate]]);
    }
}
?>
<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/rating/Model/RatingTranslator.php';
require_once 'Modules/rating/Model/Rating.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreTranslator.php';

require_once 'Modules/booking/Model/BkCalendarEntry.php';

/**
 * 
 * Controller for the rating page
 */
class RatingController extends CoresecureController {

    public function sideMenu() {
        $id_space = $this->args['id_space'];
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("rating", $id_space);
        $modelConfig = new CoreConfig();
        $title = $modelConfig->getParamSpace("ratingMenuName", $id_space);
        if($title == ""){
            $title = ClientsTranslator::clients($lang);
        }
        $dataView = [
            'id_space' => $id_space,
            'title' => $title,
            'glyphicon' => $menuInfo['icon'],
            'bgcolor' => $menuInfo['color'],
            'color' => $menuInfo['txtcolor'] ?? '',

        ];
        return $this->twig->render("Modules/rating/View/Rating/navbar.twig", $dataView);
    }


    public function campaignsAction($id_space) {
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if(!$plan->hasFlag(CorePlan::FLAGS_SATISFACTION)) {
            throw new PfmAuthException('Sorry, space does not have this feature plan');
        }
        $this->checkAuthorizationMenuSpace("rating", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $cm = new RatingCampaign();
        $campaigns = $cm->list($id_space);
        $dataTable = new TableView();
        $dataTable->setTitle(RatingTranslator::Campaigns($lang));

        $data = $campaigns;

        for($i=0;$i<count($data); $i++){
            if($data[$i]['from_date'] ?? '') {
                $data[$i]['from_date'] = CoreTranslator::dateFromEn(date('Y-m-d', $data[$i]['from_date']), $lang);
            }
            if($data[$i]['to_date'] ?? '') {
                $data[$i]['to_date'] = CoreTranslator::dateFromEn(date('Y-m-d', $data[$i]['to_date']), $lang);
            }
            if($data[$i]['limit_date'] ?? '') {
                $data[$i]['limit_date'] = CoreTranslator::dateFromEn(date('Y-m-d', $data[$i]['limit_date']), $lang);
            }
        }

        $headers = array(
            "id" => 'ID',
            "from_date" => RatingTranslator::From($lang),
            "to_date" => RatingTranslator::To($lang),
            "limit_date" => RatingTranslator::Deadline($lang)
        );
        $dataTable->addLineButton("rating/" . $id_space . "/campaign", "id", CoreTranslator::Access($lang));

        $tableHtml = $dataTable->view($data, $headers);
        $this->render(['table' => $tableHtml, 'data' => ['campaigns' => $campaigns]]);
    }

    public function campaignAction($id_space, $id_campaign) {
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if(!$plan->hasFlag(CorePlan::FLAGS_SATISFACTION)) {
            throw new PfmAuthException('Sorry, space does not have this feature plan');
        }
        $this->checkAuthorizationMenuSpace("rating", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        $cm = new RatingCampaign();
        $data = $cm->get($id_space, $id_campaign);
        $form = new Form($this->request, "campaignedit");
        $form->addHidden("id", $id_campaign);

        $from_date_str = '';
        $to_date_str = '';
        $limit_date_str = '';
        if($data['from_date'] ?? '') {
            $from_date_str = date('Y-m-d', $data['from_date']);
        }
        if($data['to_date'] ?? '') {
            $to_date_str = date('Y-m-d', $data['to_date']);
        }
        if($data['limit_date'] ?? '') {
            $limit_date_str = date('Y-m-d', $data['limit_date']);
        }
        
        if(!$id_campaign){
            $form->addDate("from_date", RatingTranslator::From($lang), true, CoreTranslator::dateFromEn($from_date_str, $lang));
            $form->addDate("to_date", RatingTranslator::To($lang), true, CoreTranslator::dateFromEn($to_date_str, $lang));
            $form->addTextArea("message", 'Message', true, $data["message"] ?? '');
        } else {
            $form->addText("from_date", RatingTranslator::From($lang), true, $from_date_str, readonly:true);
            $form->addText("to_date", RatingTranslator::To($lang), true, $to_date_str, readonly:true);
            $form->addText("message", 'Message', false, $data["message"] ?? '', readonly:true);
        }
        $form->addDate("limit_date", RatingTranslator::Deadline($lang), false, $limit_date_str);

        $form->setValidationButton(CoreTranslator::Save($lang), "rating/".$id_space.'/campaign/'.$id_campaign);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "rating/" . $id_space);

        if($form->check()){
            if($id_campaign) {
                $this->request->setParam('from_date', $data['from_date']);
                $this->request->setParam('to_date', $data['to_date']);
                $this->request->setParam('message', $data['message']);
                $_SESSION['flash'] = RatingTranslator::CampaignSaved($lang);

            } else {
                $_SESSION['flash'] = RatingTranslator::CampaignStarted($lang);
            }

            $from_date = strtotime($this->request->getParameter('from_date'));
            $to_date = strtotime($this->request->getParameter('to_date'));
            $limit_date = 0;
            if($this->request->getParameterNoException('limit_date')) {
                $limit_date = strtotime($this->request->getParameter('limit_date'));
            }

            $new_campaign = $cm->set(
                $id_space,
                $id_campaign,
                $from_date,
                $to_date,
                $limit_date,
                $this->request->getParameter('message'),
            );
            if($id_campaign == 0 && $new_campaign) {
                Events::send([
                    "action" => Events::ACTION_RATING_CAMPAIGN_NEW,
                    "space" => ["id" => intval($id_space)],
                    "campaign" => ["id" => intval($new_campaign)]
                ]);
            }

            $_SESSION['flashClass'] = 'success';
            $this->redirect('rating/'.$id_space);
        }


        $this->render(['data' => ['campaign' => $data], 'form' => $form->getHtml($lang)]);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function surveyAction($id_space, $id_campaign) {
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if(!$plan->hasFlag(CorePlan::FLAGS_SATISFACTION)) {
            throw new PfmAuthException('Sorry, space does not have this feature plan');
        }
        $role = $this->getUserSpaceStatus($id_space, $_SESSION['id_user']);
        if($role < CoreSpace::$USER) {
            throw new PfmAuthException('Access not allowed');
        }

        $cm = new RatingCampaign();
        $campaign = $cm->get($id_space, $id_campaign);
        if(!$campaign) {
            throw new PfmParamException('Survey not found');
        }
        if($campaign['limit_date'] && $campaign['limit_date'] < time()){
            throw new PfmAuthException('Survey expired!');
        }
        $bke = new BkCalendarEntry();
        $bkentries = $bke->getEntriesForPeriod($id_space, $_SESSION['id_user'], $campaign['from_date'], $campaign['to_date']);
        //             $sql = 'SELECT bk_calendar_entry.resource_id , core_users.id as user_id, core_users.login as user_login, core_users.email as user_email, re_info.name as resource_name FROM bk_calendar_entry

        $resources = [];
        foreach($bkentries as $entry) {
            if(isset($entry['resource_id'])) {  
                $resources[$entry['resource_id']] = [
                    'id' => null,
                    'name' => $entry['resource_name'],
                    'rate' => null,
                    'comment' => null,
                    'anon' => true,
                ];
            }
        }

        $r = new Rating();
        $stats = $r->list($id_space, campaign: $campaign['id']);
        foreach($stats as $i => $stat){
            if(isset($resources[$stat['resource']])) {
                $resources[$stat['resource']]['id'] = $stat['id'];
                $resources[$stat['resource']]['rate'] = round($stat['rate']);
                $resources[$stat['resource']]['comment'] = $stat['comment'];
                $resources[$stat['resource']]['anon'] = $stat['anon'];
            }
        }
        return $this->render(['data' => ['resources' => $resources]]);
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
            $r->set($id_space, 0, $_SESSION['id_user'], $module, $resource, $resourceName, $rateval, $comment, $anon);
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
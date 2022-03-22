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
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Model/ServicesTranslator.php';


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
            'manager' => $this->role >= CoreSpace::$MANAGER

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

        $campaigns = [];
        if($this->role < CoreSpace::$MANAGER){
            $campaigns = $cm->list($id_space, open: true);
        } else {
            $campaigns = $cm->list($id_space);
        }

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

        if($this->role < CoreSpace::$MANAGER){
            $dataTable->addLineButton("rating/" . $id_space . "/rate", "id", CoreTranslator::Access($lang));
        } else {
            $dataTable->addLineButton("rating/" . $id_space . "/campaign", "id", CoreTranslator::Access($lang));
        }

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

        $r = new Rating();
        $booking_ratings = $r->list($id_space, 'booking', campaign:$id_campaign);
        for($i=0;$i<count($booking_ratings);$i++){
            $booking_ratings[$i]['rate'] = intval($booking_ratings[$i]['rate']);
            if(!$booking_ratings[$i]['login'] || $booking_ratings[$i]['anon']) {
                $booking_ratings[$i]['login'] = '';
            }
        }

        $projects_ratings = $r->list($id_space, 'projects', campaign:$id_campaign);
        for($i=0;$i<count($projects_ratings);$i++){
            $projects_ratings[$i]['rate'] = intval($projects_ratings[$i]['rate']);
            if(!$projects_ratings[$i]['login'] || $projects_ratings[$i]['anon']) {
                $projects_ratings[$i]['login'] = '';
            }
        }

        $global_stats = $r->stat($id_space, $id_campaign);

        $global_bookings = [];
        foreach($global_stats as $g) {
            if($g['module'] == 'booking') {
                $g['rate'] = round($g['rate']);
                $global_bookings[] = $g;
            }
        }

        $global_projects = [];
        foreach($global_stats as $g) {
            if($g['module'] == 'projects') {
                $g['rate'] = round($g['rate']);
                $global_projects[] = $g;
            }
        }

        $total_stats = $r->statGlobal($id_space, $id_campaign);
        $total = [];
        foreach ($total_stats as $stat) {
            $stat['rate'] = round($stat['rate']);
            $total[$stat['module']] = $stat;
        }


        $this->render(['data' => ['total' => $total, 'global_projects' => $global_projects, 'global_bookings' => $global_bookings, 'campaign' => $data, 'bookings' => $booking_ratings, 'projects' => $projects_ratings], 'lang' => $lang, 'form' => $form->getHtml($lang)]);
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
        $lang = $this->getLanguage();
        $cm = new RatingCampaign();
        $campaign = $cm->get($id_space, $id_campaign);
        if(!$campaign) {
            throw new PfmParamException('Survey not found');
        }
        if($campaign['limit_date'] && $campaign['limit_date'] < time()){
            throw new PfmParamException('Survey expired!');
        }
        $bke = new BkCalendarEntry();
        $bkentries = $bke->getEntriesForPeriod($id_space, $_SESSION['id_user'], $campaign['from_date'], $campaign['to_date']);
        //             $sql = 'SELECT bk_calendar_entry.resource_id , core_users.id as user_id, core_users.login as user_login, core_users.email as user_email, re_info.name as resource_name FROM bk_calendar_entry

        $resources = [];
        foreach($bkentries as $entry) {
            if(isset($entry['resource_id'])) {  
                $resources[$entry['resource_id']] = [
                    'vid' => intval($entry['resource_id']),
                    'id' => 0,
                    'module' => 'booking',
                    'name' => $entry['resource_name'],
                    'rate' => 0,
                    'comment' => null,
                    'anon' => 1,
                ];
            }
        }

        // TODO add projects
        // closedProjectsByPeriod
        $s = new SeProject();
        $closed_projects = $s->closedProjectsByPeriod($id_space, $_SESSION['id_user'], date('Y-m-d', $campaign['from_date']), date('Y-m-d', $campaign['to_date']));
        $projects = [];
        foreach($closed_projects as $entry) {
                $projects[$entry['id']] = [
                    'vid' => intval($entry['id']),
                    'id' => 0,
                    'module' => 'projects',
                    'name' => $entry['name'],
                    'rate' => 0,
                    'comment' => null,
                    'anon' => 1,
                ];
        }

        $r = new Rating();
        $stats = $r->list($id_space, campaign: $campaign['id']);
        foreach($stats as $stat){
            if($stat['module'] == 'booking' && isset($resources[$stat['resource']])) {
                    $resources[$stat['resource']]['id'] = $stat['id'];
                    $resources[$stat['resource']]['rate'] = round($stat['rate']);
                    $resources[$stat['resource']]['comment'] = $stat['comment'];
                    $resources[$stat['resource']]['anon'] = $stat['anon'];
                }
            if($stat['module'] == 'projects' && isset($projects[$stat['resource']])) {
                $projects[$stat['resource']]['id'] = $stat['id'];
                $projects[$stat['resource']]['rate'] = round($stat['rate']);
                $projects[$stat['resource']]['comment'] = $stat['comment'];
                $projects[$stat['resource']]['anon'] = $stat['anon'];
            }
        }
        return $this->render(['lang' => $lang, 'campaign' => $id_campaign, 'data' => ['resources' => $resources, 'projects' => $projects]]);
    }



    public function rateAction($id_space, $id_campaign) {
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if(!$plan->hasFlag(CorePlan::FLAGS_SATISFACTION)) {
            throw new PfmAuthException('Sorry, space does not have this feature plan');
        }
        $userSpaceStatus = $this->getUserSpaceStatus($id_space, $_SESSION["id_user"]);
        if($userSpaceStatus != CoreSpace::$USER) {
            throw new PfmAuthException("only user space member can evaluate!");
        }
        $c = new RatingCampaign();
        $campaign = $c->get($id_space, $id_campaign);
        if(!$campaign) {
            throw new PfmParamException("Survey not found");
        }
        if($campaign['limit_date'] < time()) {
            throw new PfmParamException("Survey expired");
        }
        $r = new Rating();
        $id_user = $_SESSION['id_user'];
        $id = $r->set(
            $id_space,
            $id_campaign,
            $id_user,
            $this->request->getParameter('id'),
            $this->request->getParameter('module'),
            $this->request->getParameter('vid'),
            $this->request->getParameter('name'),
            $this->request->getParameter('rate'),
            $this->request->getParameterNoException('comment'),
            $this->request->getParameter('anon'),
        );
        return $this->render(['data' => ['rate' => ['id' => $id]]]);
    }
}
?>
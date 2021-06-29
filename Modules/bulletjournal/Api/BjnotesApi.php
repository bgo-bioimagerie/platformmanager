<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/bulletjournal/Model/BulletjournalTranslator.php';
require_once 'Modules/bulletjournal/Model/Bjnote.php';
require_once 'Modules/bulletjournal/Model/BjTask.php';
require_once 'Modules/bulletjournal/Model/BjEvent.php';
require_once 'Modules/bulletjournal/Model/BjTaskHistory.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BjnotesApi extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("bulletjournal");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function editnotequeryAction($id_space) {
        $this->checkAuthorizationMenuSpace("bulletjournal", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();


        $modelNote = new BjNote();
        $id = $this->request->getParameter("formnoteid");
        $isedit = 1;
        if(!$id){
            $isedit = 0;
        }
        
        $ismonth = $this->request->getParameter("formnoteismonth");
        $name = $this->request->getParameter("formnotename");
        $type = 1;
        $content = $this->request->getParameter("formnotecontent");
        $date = CoreTranslator::dateToEn($this->request->getParameter("formnotedate"), $lang);
        $modelNote->set($id, $id_space, $name, $type, $content, $date, $ismonth);

        $datearray = explode("-", $date);

        $data = array("id" => $id, "name" => $name, "type" => $type,
            "content" => $content, "date" => $date,
            "year" => $datearray[0], "month" => $datearray[1], "day" => $datearray[2],
            "isedit" => $isedit, "ismonth" => $ismonth);
        echo json_encode($data);
    }

    public function getnoteAction($id) {
        $modelNote = new BjNote();

        $data = $modelNote->get($id);

        $lang = $this->getLanguage();
        $data["date"] = CoreTranslator::dateFromEn($data["date"], $lang);

        echo json_encode($data);
    }

    public function edittaskAction($id_space) {
        $this->checkAuthorizationMenuSpace("bulletjournal", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelNote = new BjNote();
        $modelTask = new BjTask();
        $id = $this->request->getParameter("formtaskid");
        $isedit = 1;
        if(!$id){
            $isedit = 0;
        }
        $name = $this->request->getParameter("formtaskname");
        $ismonth = $this->request->getParameter("formtaskismonth");
        $type = 2;
        $content = $this->request->getParameter("formtaskcontent");
        $date = CoreTranslator::dateToEn($this->request->getParameter("formtaskdate"), $lang);
        $id_note = $modelNote->set($id, $id_space, $name, $type, $content, $date, $ismonth);

        $priority = $this->request->getParameter("formtaskpriority");
        $deadline = CoreTranslator::dateToEn($this->request->getParameter("formtaskdeadline"), $lang);
        $modelTask->set($id_note, $priority, $deadline);

        $datearray = explode("-", $date);

        $data = array("id" => $id, "name" => $name, "type" => $type,
            "content" => $content, "date" => $date,
            "priority" => $priority, "deadline" => $deadline,
            "year" => $datearray[0], "month" => $datearray[1], "day" => $datearray[2],
            "isedit" => $isedit, "ismonth" => $ismonth);
        echo json_encode($data);
    }

    public function gettaskAction($id) {
        $modelTask = new BjTask();
        $data = $modelTask->getForNote($id);

        $lang = $this->getLanguage();
        $data["date"] = CoreTranslator::dateFromEn($data["date"], $lang);
        $data["deadline"] = CoreTranslator::dateFromEn($data["deadline"], $lang);

        echo json_encode($data);
    }
    
    public function closetaskAction($id){
        $modelTaskHistory = new BjTaskHistory();
        $data = $modelTaskHistory->getForNote($id);
        
        $lastStatus = 1;
        if (count($data) > 0){
            $lastStatus = $data[0]["status"];
        }
        $status = 1;
        if($lastStatus == 1){
            $status = 2;
        }
        $modelTaskHistory->addHist( $id, $status, time() );
        
        $dataout = array("status" => $status);
        echo json_encode($dataout);
    }
    
    public function canceltaskAction($id){
        $modelTaskHistory = new BjTaskHistory();
        $data = $modelTaskHistory->getForNote($id);
        
        $lastStatus = 1;
        if (count($data) > 0){
            $lastStatus = $data[0]["status"];
        }
        $status = 1;
        if($lastStatus < 3){
            $status = 3;
        }
        $modelTaskHistory->addHist( $id, $status, time() );
        
        $dataout = array("status" => $status);
        echo json_encode($dataout);
    }

    public function geteventAction($id) {

        $modelTask = new BjEvent();
        $data = $modelTask->getForNote($id);

        $lang = $this->getLanguage();
        $data["startdate"] = CoreTranslator::dateFromEn(date('Y-m-d', $data["start_time"]), $lang);
        $data["starthour"] = CoreTranslator::dateFromEn(date('H', $data["start_time"]), $lang);
        $data["startmin"] = CoreTranslator::dateFromEn(date('i', $data["start_time"]), $lang);
        
        $data["enddate"] = CoreTranslator::dateFromEn(date('Y-m-d', $data["end_time"]), $lang);
        $data["endhour"] = CoreTranslator::dateFromEn(date('H', $data["end_time"]), $lang);
        $data["endmin"] = CoreTranslator::dateFromEn(date('i', $data["end_time"]), $lang);
        
        echo json_encode($data);
    }
    
    public function editeventAction($id_space) {
        $this->checkAuthorizationMenuSpace("bulletjournal", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelNote = new BjNote();
        $id = $this->request->getParameter("formeventid");
        $isedit = 1;
        if(!$id){
            $isedit = 0;
        }
        $name = $this->request->getParameter("formeventname");
        $ismonth = $this->request->getParameter("formeventismonth");
        $type = 3;
        $content = $this->request->getParameter("formeventcontent");
        $date = CoreTranslator::dateToEn($this->request->getParameter("formeventdatestart"), $lang);
        $id_note = $modelNote->set($id, $id_space, $name, $type, $content, $date, $ismonth);

        $modelEvent = new BjEvent();
        $start_hour = $this->request->getParameter("formeventdatestartH");
        $start_min = $this->request->getParameter("formeventdatestartm");
        $end_hour = $this->request->getParameter("formeventdateendH");
        $end_min = $this->request->getParameter("formeventdateendm");
        $datearray = explode("-", $date);
        $enddate = CoreTranslator::dateToEn($this->request->getParameter("formeventdateend"), $lang);
        $enddatearray = explode("-", $enddate);
        
        $start_time = mktime($start_hour, $start_min, 0, $datearray[1], $datearray[2], $datearray[0]);
        $end_time = mktime($end_hour, $end_min, 0, $enddatearray[1], $enddatearray[2], $enddatearray[0]);
        
        $modelEvent->set($id_note, $start_time, $end_time);
        
        $data = array("id" => $id, "name" => $name, "type" => $type,
            "content" => $content, "date" => $date,
            "year" => $datearray[0], "month" => $datearray[1], "day" => $datearray[2],
            "start_time" => $start_time, "end_time" => $end_time,
            "isedit" => $isedit, "ismonth" => $ismonth);
        echo json_encode($data);
    }

}

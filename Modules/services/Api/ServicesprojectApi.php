<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/SeProject.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicesprojectApi extends CoresecureController {

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
    public function editentryqueryAction($id_space) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelProject = new SeProject();
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

    public function getprojectentryAction($id, $id_space) {
        
        $lang = $this->getLanguage();
        $modelProject = new SeProject();

        $data = $modelProject->getProjectEntry($id_space, $id);
        $data["date"] = CoreTranslator::dateFromEn($data["date"], $lang);
        
        echo json_encode($data);
    }
}

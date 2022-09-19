<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/booking/Model/BkScheduling.php';
require_once 'Modules/booking/Model/BkColorCode.php';
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ResourceInfo.php';

require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/booking/Controller/BookingsettingsController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingschedulingController extends BookingsettingsController {
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);
        
        $lang = $this->getLanguage();
        
        $modelArea = new ReArea();
        $areas = $modelArea->getForSpace($id_space);

        $modelResource = new ResourceInfo();
        $resources = $modelResource->getForSpace($id_space);

        $bk = new BkScheduling();
        $bkcalendars = $bk->getAll($id_space);
        $bks = new BkResourceSchedule();
        $defScheduling = $bks->getDefault($id_space);
        if ($defScheduling == null) {
            throw new PfmException("No default scheduling found", 500);
        }
        $def = $defScheduling['id_bkschedule'];
        $bklist = [$def => "default"];
        $resbklist = [0 => "parent area"];
        foreach ($bkcalendars as $cal) {
            $bklist[$cal['id']] = $cal['name'];
            $resbklist[$cal['id']] = $cal['name'];
        }

        
        $calendars = $bks->all($id_space);
        $resourcesCalendars = [];
        $reareasCalendars = [];
        foreach ($calendars as $cal) {
            if($cal['id_resource'] == 0 && $cal['id_rearea'] == 0) {
                continue;
            }
            if($cal['id_resource'] > 0){
                $resourcesCalendars[$cal['id_resource']] = $cal['id_bkschedule'];
            }
            if($cal['id_rearea'] > 0){
                $reareasCalendars[$cal['id_rearea']] = $cal['id_bkschedule'];
            }
        }

        $mareas = [];

        foreach ($areas as $i => $r) {
            $areas[$i]['calendar'] = $reareasCalendars[$r['id']] ?? $def;
            $mareas[$areas[$i]['id']] = $areas[$i];
        }

        foreach ($resources as $i => $r) {
            if(isset($resourcesCalendars[$r['id']])) {
                $resources[$i]['calendar'] = $resourcesCalendars[$r['id']];
            } else {
                $resources[$i]['calendar'] = 0;
            }
            $mareas[$resources[$i]['id_area']]['resources'][] = $resources[$i];
        }

        if (empty($areas)) {
            $_SESSION['flash'] = ResourcesTranslator::Area_Needed($lang);
            $_SESSION['flashClass'] = "warning";
        }
        
        $table = new TableView();
        $table->setTitle(BookingTranslator::Scheduling($lang), 3);
        $table->addLineEditButton("bookingschedulingedit/".$id_space);
        
        $headers = array("name" => CoreTranslator::Name($lang));
        
        $tableHtml = $table->view($areas, $headers);
        
        $this->render(array("id_space" => $id_space, "lang" => $lang, "tableHtml" => $tableHtml, "areas" => $mareas, "calendars" => $bklist, 'rescalendars' => $resbklist));
    }
    
    public function editAction($id_space, $id) {
        // That to avoid some confusions between id_rearea and bkScheduling['id']
        // $id_rearea = $id;
        
        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        /*
        $modelArea = new ReArea();
        $area = $modelArea->get($id_space, $id_rearea);
        if (!$area) {
            throw new PfmUserException(ResourcesTranslator::AreaNotAuthorized($lang), 403);
        }
        
        $name = $area['name'];
        */
        
        $modelScheduling = new BkScheduling();
        $bkScheduling = $modelScheduling->get($id_space, $id);
        if ($bkScheduling['id_space'] == 0) {
            $bkScheduling["id_space"] = $id_space;
        }
        $name = $bkScheduling['name'];

        $form = new Form($this->request, "bookingschedulingedit");
        $form->setTitle(BookingTranslator::Edit_scheduling($lang) . ": " . $name, 3);
        $form->addHidden("id", $bkScheduling['id'] ?? 0);
        $form->addText("name", CoreTranslator::Name($lang), true, $bkScheduling['name'] ?? '');
        $form->addChoicesList(BookingTranslator::Availables_days($lang),
                BookingTranslator::DaysList($lang), 
                array("is_monday", "is_tuesday", "is_wednesday", "is_thursday", "is_friday", "is_saturday", "is_sunday"),
                array($bkScheduling["is_monday"], $bkScheduling["is_tuesday"], $bkScheduling["is_wednesday"], $bkScheduling["is_thursday"], $bkScheduling["is_friday"], $bkScheduling["is_saturday"], $bkScheduling["is_sunday"])
                );
        
        $dc = array(); $dcid = array();
        for ($d = 0 ; $d < 25 ; $d++){
            $dc[] = $d . "h";
            $dcid[] = $d;
        }
        $form->addSelect("day_begin", BookingTranslator::Day_beginning($lang), $dc, $dcid, $bkScheduling["day_begin"]);
        $form->addSelect("day_end", BookingTranslator::Day_end($lang), $dc, $dcid, $bkScheduling["day_end"]);
        $form->addSelect("size_bloc_resa", BookingTranslator::Booking_size_bloc($lang), array("15min", "30min", "1h"), array(900, 1800, 3600), $bkScheduling["size_bloc_resa"]);
        $form->addSelect("booking_time_scale", BookingTranslator::Booking_time_scale($lang), array(BookingTranslator::Minutes($lang), BookingTranslator::Hours($lang), BookingTranslator::Days($lang)), array(1, 2, 3), $bkScheduling["booking_time_scale"]);
        if($bkScheduling["force_packages"] ?? 0) {
            $form->addHidden("resa_time_setting", $bkScheduling["resa_time_setting"]);
        } else {
            $form->addSelect("resa_time_setting", BookingTranslator::The_user_specify($lang), array(BookingTranslator::the_booking_duration($lang), BookingTranslator::the_date_time_when_reservation_ends($lang)), array(1, 2), $bkScheduling["resa_time_setting"]);
        }
        $form->addSelect("force_packages", BookingTranslator::Force_packages($lang), array(CoreTranslator::no($lang), CoreTranslator::yes($lang)), array(0, 1), $bkScheduling["force_packages"] ?? 0);
        $form->addSelect("shared", "Share between resources", [CoreTranslator::yes($lang), CoreTranslator::no($lang)], [1,0], $bkScheduling['shared']);

        $modelColor = new BkColorCode();
        $colors = $modelColor->getForSpace($id_space);

        // if no color code was created, then alert user it is required to do so
        if (!$colors) {
            // display alert
            $_SESSION["flash"] = BookingTranslator::MissingColorCode($lang);
        }
        
        $cc = array(); $ccid = array();
        foreach($colors as $color){
            $cc[] = $color["name"];
            $ccid[] = $color["id"];
        }
        $form->addSelectMandatory("default_color_id", BookingTranslator::Default_color($lang), $cc, $ccid, $bkScheduling["default_color_id"]);
        
        $todo = $this->request->getParameterNoException('redirect');
        $validationUrl = "bookingschedulingedit/".$id_space."/".$id;
        if ($todo) {
            $validationUrl .= "?redirect=todo";
        }

        $form->setValidationButton(CoreTranslator::Save($lang), $validationUrl);
        $form->setColumnsWidth(3, 9);



        if($bkScheduling["force_packages"] ?? 0) {
            $modelPackage = new BkPackage();
            $packages = $modelPackage->getAll($id_space, 'id');
            if(empty($packages)) {
                $_SESSION['flash'] = BookingTranslator::MissingPackages($lang);
            }
        }


        if ($form->check()) {
            $id_bkScheduling = $modelScheduling->edit($id_space,
                    $this->request->getParameterNoException("id"), 
                    $this->request->getParameterNoException("name"), 
                    $this->request->getParameterNoException("is_monday"), 
                    $this->request->getParameterNoException("is_tuesday"), 
                    $this->request->getParameterNoException("is_wednesday"), 
                    $this->request->getParameterNoException("is_thursday"), 
                    $this->request->getParameterNoException("is_friday"), 
                    $this->request->getParameterNoException("is_saturday"), 
                    $this->request->getParameterNoException("is_sunday"), 
                    $this->request->getParameter("day_begin"), 
                    $this->request->getParameter("day_end"), 
                    $this->request->getParameter("size_bloc_resa"), 
                    $this->request->getParameter("booking_time_scale"), 
                    $this->request->getParameter("resa_time_setting"), 
                    $this->request->getParameter("default_color_id"),
                    $this->request->getParameterNoException("force_packages"),
                    $this->request->getParameter("shared")
            );

            $_SESSION["flash"] = BookingTranslator::Item_created("schedule", $lang);
            $_SESSION["flashClass"] = "success";
                
            if ($todo) {
                return $this->redirect("spaceadminedit/" . $id_space, ["showTodo" => true]);
            } else {
                return $this->redirect("bookingschedulingedit/".$id_space."/".$id_bkScheduling, [], ['bkScheduling' => ['id' => $id_bkScheduling]]);
            }
            
        }
         
        return $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "htmlForm" => $form->getHtml($lang),
            "data" => ["bkScheduling" => $bkScheduling]
        ));
        
    }

    public function assignAction($kind, $id_space, $id, $id_calendar) {
        $bks = new BkResourceSchedule();
        $link = 0;
        if ($kind == 0) {
            // area
            $link = $bks->linkArea($id_space, $id, $id_calendar);
        } else if ($kind == 1) {
            // resource
            if ($id_calendar == 0) {
                $bks->unlinkResource($id_space, $id);
            } else {
                $link = $bks->linkResource($id_space, $id, $id_calendar);
            }
        }

        return $this->render(['data' => ['id' => $link]]);
    }

    public function deleteAction($id_space, $id) {
        $bk= new BkScheduling();
        $bk->delete($id_space, $id);
        $this->redirect('/bookingscheduling/'.$id_space);
    }
}

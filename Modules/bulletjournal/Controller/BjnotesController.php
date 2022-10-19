<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/bulletjournal/Model/BulletjournalTranslator.php';
require_once 'Modules/bulletjournal/Model/BjNote.php';
require_once 'Modules/bulletjournal/Model/BjEvent.php';
require_once 'Modules/bulletjournal/Model/BjTask.php';

require_once 'Modules/core/Controller/CorespaceController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BjnotesController extends CoresecureController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace, $year, $month)
    {
        $this->checkAuthorizationMenuSpace("bulletjournal", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        if ($month == "" || $month == 0) {
            $month = date('m', time());
        }
        if ($year == "" || $year == 0) {
            $year = date('Y', time());
        }

        $modelNote = new BjNote();

        $notes = $modelNote->getAllForMonth($idSpace, $month, $year);

        // edit note form
        $noteForm = $this->createNoteForm($idSpace, $lang);
        $taskForm = $this->createTaskForm($idSpace, $lang);
        $eventForm = $this->createEventForm($idSpace, $lang);

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "month" => $month,
            "year" => $year,
            "notes" => $notes, "noteForm" => $noteForm,
            "taskForm" => $taskForm, "eventForm" => $eventForm), "indexAction");
    }

    public function monthbeforeAction($idSpace, $year, $month)
    {
        if ($month == 1) {
            $year = $year-1;
            $month = 12;
        } else {
            $month = $month -1;
        }

        $this->indexAction($idSpace, $year, $month);
    }

    public function monthafterAction($idSpace, $year, $month)
    {
        if ($month == 12) {
            $year = $year+1;
            $month = 1;
        } else {
            $month = $month + 1;
        }

        $this->indexAction($idSpace, $year, $month);
    }


    public function createNoteForm($idSpace, $lang)
    {
        $form = new Form($this->request, "editNoteForm", true);
        $form->addHidden("formnoteid", 0);
        $form->addHidden("formnoteismonth", 0);
        $form->addText("formnotename", BulletjournalTranslator::Title($lang), true);
        $form->addDate("formnotedate", CoreTranslator::Date($lang), true, "");
        $form->addTextArea("formnotecontent", BulletjournalTranslator::Content($lang), false, "", false);
        $form->setColumnsWidth(2, 9);

        $form->setValidationButton(CoreTranslator::Save($lang), "bjeditnotequery/".$idSpace);
        return $form->getHtml($lang);
    }

    public function createTaskForm($idSpace, $lang)
    {
        $form = new Form($this->request, "editTaskForm", true);
        $form->addHidden("formtaskid", 0);
        $form->addHidden("formtaskismonth", 0);
        $form->addText("formtaskname", BulletjournalTranslator::Title($lang), true);
        $form->addNumber("formtaskpriority", BulletjournalTranslator::Priority($lang));
        $form->addDate("formtaskdate", CoreTranslator::Date($lang), true, "");
        $form->addDate("formtaskdeadline", BulletjournalTranslator::Deadline($lang), false, "");
        $form->addTextArea("formtaskcontent", BulletjournalTranslator::Content($lang), false, "", false);
        $form->setColumnsWidth(2, 9);

        $form->setValidationButton(CoreTranslator::Save($lang), "bjedittask/".$idSpace);
        return $form->getHtml($lang);
    }

    public function createEventForm($idSpace, $lang)
    {
        $form = new Form($this->request, "editEventForm", true);
        $form->addHidden("formeventid", 0);
        $form->addHidden("formeventismonth", 0);
        $form->addText("formeventname", BulletjournalTranslator::Title($lang), true);
        $form->addDatetime("formeventdatestart", BulletjournalTranslator::DateStart($lang), true, array("", "", ""));
        //$form->addHour("formeventdatestarthour", BulletjournalTranslator::HourStart($lang), true);
        $form->addDatetime("formeventdateend", BulletjournalTranslator::DateEnd($lang), true, array("", "", ""));
        //$form->addHour("formeventdateendhour", BulletjournalTranslator::HourEnd($lang), true);
        $form->addTextArea("formeventcontent", BulletjournalTranslator::Content($lang), false, "", false);
        $form->setColumnsWidth(2, 9);

        $form->setValidationButton(CoreTranslator::Save($lang), "bjeditevent/".$idSpace);
        return $form->getHtml($lang);
    }

    public function deletenoteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("bulletjournal", $idSpace, $_SESSION["id_user"]);
        $modelNote = new BjNote();
        $modelNote->delete($idSpace, $id);
        $modelEvent = new BjEvent();
        $modelEvent->delete($idSpace, $id);
        $modelTask = new BjTask();
        $modelTask->delete($idSpace, $id);
        return null;
    }
}

<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/booking/Model/BkRestrictions.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/booking/Controller/BookingsettingsController.php';

use Khill\Duration\Duration;

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BookingrestrictionsController extends BookingsettingsController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $model = new BkRestrictions();
        $modelResource = new ResourceInfo();
        $model->init($id_space);
        $data = $model->getForSpace($id_space);
        for ($i = 0 ; $i < count($data); $i++) {
            $data[$i]["resource"] = $modelResource->getName($id_space, $data[$i]["id_resource"]);
            if ($data[$i]['maxduration']) {
                $duration = new Duration($data[$i]['maxduration']);
                $ts = $duration->toSeconds();
                $data[$i]['maxduration'] .= ' ('.$ts.'s)';
            }
            $data[$i]['maxfulldays'] = $data[$i]['maxfulldays'] ? CoreTranslator::yes($lang) : CoreTranslator::no($lang);
            $data[$i]['disableoverclosed'] = $data[$i]['disableoverclosed'] ? CoreTranslator::yes($lang) : CoreTranslator::no($lang);

        }

        $table = new TableView();
        $table->setTitle(BookingTranslator::BookingRestriction($lang));
        $headers = array(
            "resource" => BookingTranslator::Resource(),
            "maxbookingperday" => BookingTranslator::Maxbookingperday($lang),
            "bookingdelayusercanedit" => BookingTranslator::BookingDelayUserCanEdit($lang),
            'maxduration' => BookingTranslator::maxDuration($lang),
            'maxfulldays' => BookingTranslator::maxFullDays($lang),
            'disableoverclosed' => BookingTranslator::disableOverClosed($lang)
        );

        $table->addLineEditButton("bookingrestrictionedit/".$id_space);
        $tableHtml = $table->view($data, $headers);


        // view
        $this->render(array("id_space" => $id_space, "tableHtml" => $tableHtml, "lang" => $lang));
    }

    public function editAction($id_space, $id)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $model = new BkRestrictions();
        $data = $model->get($id_space, $id);

        $modelResource = new ResourceInfo();
        $resource = $modelResource->get($id_space, $data['id_resource']);
        $resourceName = $resource['name'];

        $form = new Form($this->request, "restrictioneditform");
        $form->setTitle(BookingTranslator::RestrictionsFor($lang) . ": " . $resourceName);

        $form->addText("maxbookingperday", BookingTranslator::Maxbookingperday($lang), false, $data["maxbookingperday"]);
        $form->addText("bookingdelayusercanedit", BookingTranslator::BookingDelayUserCanEdit($lang), false, $data["bookingdelayusercanedit"]);
        $form->addText("maxduration", BookingTranslator::maxDuration($lang), false, $data["maxduration"]);
        $form->addSelect("maxfulldays", BookingTranslator::maxFullDays($lang), [CoreTranslator::yes($lang), CoreTranslator::no($lang)], [1, 0], $data['maxfulldays']);
        $form->addSelect("disableoverclosed", BookingTranslator::disableOverClosed($lang), [CoreTranslator::no($lang), CoreTranslator::yes($lang)], [0, 1], $data['disableoverclosed']);

        $form->setValidationButton(CoreTranslator::Save($lang), "bookingrestrictionedit/".$id_space."/".$id);

        if ($form->check()) {
            $maxbookingperday = $this->request->getParameter("maxbookingperday");
            $bookingdelayusercanedit = $this->request->getParameter("bookingdelayusercanedit");
            $maxduration = $this->request->getParameterNoException("maxduration", '');

            if($maxduration) {
                try {
                    $duration = new Duration($maxduration);
                    $ts = $duration->toSeconds();
                    if($ts == 0) {
                        throw new PfmParamException('Invalid duration: '.$maxduration);
                    }
                    Configuration::getLogger()->error('????????', ['d' => $ts]);
                } catch(Throwable $e) {
                    throw new PfmParamException('Invalid duration: '.$maxduration);
                }
            }

            $maxfulldays = $this->request->getParameterNoException("maxfulldays", 0);
            $disableoverclosed = $this->request->getParameterNoException("disableoverclosed", 0);
            $model->set($id_space, $id, $maxbookingperday, $bookingdelayusercanedit, $maxduration, $maxfulldays, $disableoverclosed);

            $this->redirect("bookingrestrictions/".$id_space);
            return;
        }

        $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'htmlForm' => $form->getHtml($lang)
            ));
    }
}

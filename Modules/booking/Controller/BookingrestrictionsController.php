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
        $displaydata = [];
        for ($i = 0 ; $i < count($data); $i++) {
            $displaydataElt = $data[$i];
            $displaydataElt["resource"] = $modelResource->getName($id_space, $displaydataElt["id_resource"]);
            if ($displaydataElt['maxduration']) {
                $duration = new Duration($displaydataElt['maxduration']);
                $ts = $duration->toSeconds();
                $displaydataElt['maxduration'] .= ' ('.$ts.'s)';
            }
            $displaydataElt['maxfulldays'] = $displaydataElt['maxfulldays'] ? CoreTranslator::yes($lang) : CoreTranslator::no($lang);
            $displaydataElt['disableoverclosed'] = $displaydataElt['disableoverclosed'] ? CoreTranslator::yes($lang) : CoreTranslator::no($lang);
            $displaydata[] = $displaydataElt;
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
        $tableHtml = $table->view($displaydata, $headers);


        // view
        return $this->render(array("data" => ['bkrestrictions' => $data], "id_space" => $id_space, "tableHtml" => $tableHtml, "lang" => $lang));
    }

    public function editAction($id_space, $id)
    {
        $this->checkAuthorizationMenuSpace("bookingsettings", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $model = new BkRestrictions();
        if ($id) {
            $data = $model->get($id_space, $id);
        } else {
            $data = $model->default($id_space, 0);
        }

        $modelResource = new ResourceInfo();
        
        $form = new Form($this->request, "restrictioneditform");
        $form->setTitle(BookingTranslator::RestrictionsFor($lang));
        $form->addHidden("id", $id);
        if ($id) {
            $resource = $modelResource->get($id_space, $data['id_resource']);
            $resourceName = $resource['name'];
            $form->addHidden("id_resource", $data['id_resource']);
            $form->addText("resource", ResourcesTranslator::resource($lang), true, $resourceName, true, true);
        } else {
            $resources = $modelResource->getForList($id_space);
            $form->addSelect('id_resource', ResourcesTranslator::resource($lang), $resources['names'], $resources['ids'], $resources['ids'][0]);
        }
        $form->addText("maxbookingperday", BookingTranslator::Maxbookingperday($lang), false, $data["maxbookingperday"]);
        $form->addText("bookingdelayusercanedit", BookingTranslator::BookingDelayUserCanEdit($lang), false, $data["bookingdelayusercanedit"]);
        $form->addText("maxduration", BookingTranslator::maxDuration($lang), false, $data["maxduration"]);
        $form->addSelect("maxfulldays", BookingTranslator::maxFullDays($lang), [CoreTranslator::yes($lang), CoreTranslator::no($lang)], [1, 0], $data['maxfulldays']);
        $form->addSelect("disableoverclosed", BookingTranslator::disableOverClosed($lang), [CoreTranslator::no($lang), CoreTranslator::yes($lang)], [0, 1], $data['disableoverclosed']);
        $modelSpace = new CoreSpace();
        $roles = $modelSpace->roles($lang, 0);
        $form->addSelect("applies_to", BookingTranslator::appliesTo($lang), $roles['names'], $roles['ids'], $data['applies_to']);


        $form->setValidationButton(CoreTranslator::Save($lang), "bookingrestrictionedit/".$id_space."/".$id);

        if ($form->check()) {
            $maxbookingperday = $this->request->getParameter("maxbookingperday");
            $bookingdelayusercanedit = $this->request->getParameter("bookingdelayusercanedit");
            $maxduration = $this->request->getParameterNoException("maxduration", '');

            if ($maxduration) {
                try {
                    $duration = new Duration($maxduration);
                    $ts = $duration->toSeconds();
                    if ($ts == 0) {
                        throw new PfmParamException('Invalid duration: '.$maxduration);
                    }
                } catch (Throwable $e) {
                    throw new PfmParamException('Invalid duration: '.$maxduration);
                }
            }

            $appliesTo = $this->request->getParameterNoException('applies_to', true, CoreSpace::$USER);
            $maxfulldays = $this->request->getParameterNoException("maxfulldays", 0);
            $disableoverclosed = $this->request->getParameterNoException("disableoverclosed", 0);
            $id = $model->set($id_space, $id, $this->request->getParameter('id_resource'), $maxbookingperday, $bookingdelayusercanedit, $maxduration, $maxfulldays, $disableoverclosed, $appliesTo);

            return $this->redirect("bookingrestrictions/".$id_space, [], ['bkrestriction' => ['id' => $id]]);
        }

        return $this->render(array(
                'id_space' => $id_space,
                'lang' => $lang,
                'htmlForm' => $form->getHtml($lang),
                'data' => ['bkrestriction' => $data]
        ));
    }
}

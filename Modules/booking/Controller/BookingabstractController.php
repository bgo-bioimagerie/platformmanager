<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BkBookingTableCSS.php';
require_once 'Modules/booking/Model/BkCalSupInfo.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';
require_once 'Modules/booking/Model/BkScheduling.php';
require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/booking/Model/BkAuthorization.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ReArea.php';

require_once 'Modules/core/Model/CoreUserSettings.php';

require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreSpace.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BookingabstractController extends CoresecureController
{
    public function spaceExtraMenus()
    {
        $lang = $this->getLanguage();
        $menu = [
            ['name' => BookingTranslator::booking($lang), 'url' => 'bookingdayarea/'.$this->currentSpace['id'].'/'],
            ['name' => BookingTranslator::journal($lang), 'url' => 'booking/'.$this->currentSpace['id'].'/journal']
        ];
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if ($plan->hasFlag(CorePlan::FLAGS_CALDAV)) {
            $menu[] = ['name' => 'CalDAV: '.Configuration::get('public_url').'/caldav/'.$this->currentSpace['id'].'/0/', 'url' => ''];
        }
        return $menu;
    }

    /**
     * Get the content of of the booking menu for the calendar pages
     * @param number $curentAreaId ID of the curent area
     * @param number $curentResourceId ID of the current resource
     * @param date $curentDate Curent date
     * @return array: booking menu content
     */
    public function calendarMenuData($curentSiteId, $curentAreaId, $curentResourceId, $curentDate)
    {
        if ($curentDate == "") {
            $curentDate = date("Y-m-d", time());
        }

        $modelArea = new ReArea();
        $areas = array();
        $modelUserSpace = new CoreSpace();
        $status = $modelUserSpace->getUserSpaceRole($curentSiteId, $_SESSION["id_user"]);

        if ($status <= 2) {
            $areas = $modelArea->getUnrestrictedAreasIDNameForSite($curentSiteId);
        } else {
            $areas = $modelArea->getAreasIDNameForSite($curentSiteId);
        }



        $foundArea = false;
        foreach ($areas as $area) {
            if ($area["id"] == $curentAreaId) {
                $foundArea = true;
            }
        }
        if (!$foundArea) {
            $curentAreaId = $areas[0]["id"];
        }

        $modelResource = new ResourceInfo();
        $resources = $modelResource->resourceIDNameForArea($curentSiteId, $curentAreaId);

        /*
        $_SESSION['bk_id_resource'] = $curentResourceId;
        $_SESSION['bk_id_area'] = $curentAreaId;
        $_SESSION['bk_id_site'] = $curentSiteId;
        $_SESSION['bk_curentDate'] = $curentDate;
        */

        return array(
            'areas' => $areas,
            'resources' => $resources,
            'curentSiteId' => $curentSiteId,
            'curentAreaId' => $curentAreaId,
            'curentResourceId' => $curentResourceId,
            'curentDate' => $curentDate,
        );
    }

    /**
     * Check if a given user is allowed to book a ressource
     * @param number $id_resourcecategory ID of the resource category
     * @param number $resourceAccess Type of users who can access the resource
     * @param number $id_user User ID
     * @param number $curentDateUnix resa date in unix format, skip check if 0
     * @return boolean
     */
    protected function hasAuthorization($id_resourcecategory, $resourceAccess, $id_space, $id_user, $curentDateUnix)
    {
        $modelSpace = new CoreSpace();
        $userSpaceRole = $modelSpace->getUserSpaceRole($id_space, $id_user);


        if ($userSpaceRole > CoreSpace::$MANAGER) {
            return true;
        }

        // user cannot book in the past
        if ($curentDateUnix > 0 && $curentDateUnix < mktime(0, 0, 0, date("m", time()), date("d", time()), date("Y", time()))) {
            return false;
        }

        // test depending the user status and resource
        $isUserAuthorizedToBook = false;
        if ($resourceAccess == 1) {
            if ($userSpaceRole > CoreSpace::$VISITOR) {
                $isUserAuthorizedToBook = true;
            }
        } elseif ($resourceAccess == 2) {
            //echo "pass 1 </Br>";
            if ($userSpaceRole > CoreSpace::$USER) {
                $isUserAuthorizedToBook = true;
            }
            if ($userSpaceRole == CoreSpace::$USER) {
                //echo "pass </Br>";
                // check if the user has been authorized
                $modelAuth = new BkAuthorization();
                $isUserAuthorizedToBook = $modelAuth->hasAuthorization($id_space, $id_resourcecategory, $id_user);
                //echo "authorized user = " . $isUserAuthorizedToBook . "";
            }
        } elseif ($resourceAccess == 3) {
            if ($userSpaceRole >= CoreSpace::$MANAGER) {
                $isUserAuthorizedToBook = true;
            }
        } elseif ($resourceAccess == 4) {
            if ($userSpaceRole >= CoreSpace::$ADMIN) {
                $isUserAuthorizedToBook = true;
            }
        }
        return $isUserAuthorizedToBook;
    }
}

<?php

require_once 'Framework/Routing.php';

class BookingRouting extends Routing{

    public function routes($router) {
        $router->map('GET', '/user/booking/future/[i:id_space]/[i:id_resource]', 'booking/booking/future', 'booking_list_future');
        $router->map('GET', '/booking/[i:id_space]/journal', 'booking/booking/journal', 'booking_journal');

        $router->map('OPTIONS', '/caldav/[i:id_space]/', 'booking/bookingcaldav/discovery', 'booking_caldav_discovery_space');
        $router->map('OPTIONS', '/caldav/[i:id_space]/0/', 'booking/bookingcaldav/discovery', 'booking_caldav_discovery_space_default');

        $router->map('PROPFIND', '/caldav/[i:id_space]/[i:id_cal]/', 'booking/bookingcaldav/propfind', 'booking_caldav_propfind');
        $router->map('PROPFIND', '/caldav/[i:id_space]/[i:id_cal]', 'booking/bookingcaldav/propfind', 'booking_caldav_propfind_notrailing'); // btsync sometimes force removal of trailing slash
        $router->map('REPORT', '/caldav/[i:id_space]/1/', 'booking/bookingcaldav/report', 'booking_caldav_report');
        $router->map('REPORT', '/caldav/[i:id_space]/0/', 'booking/bookingcaldav/report', 'booking_caldav_report_default');
    }

    
    public function listRoutes(){
        
        // config
        $this->addRoute("bookingconfig", "bookingconfig", "bookingconfig", "index", array("id_space"), array(""));
        $this->addRoute("bookingsettingsconfig", "bookingsettingsconfig", "bookingconfig", "index", array("id_space"), array(""));
        
        // user settings
        $this->addRoute("bookingusersettings", "bookingusersettings", "bookingusersettings", "index");
        
        // add here the module routes
        $this->addRoute("booking", "booking", "booking", "index", array("id_space"), array(""));
        
        $this->addRoute("bookingsettings", "bookingsettings", "bookingsettings", "index", array("id_space"), array(""));
        $this->addRoute("bookingscheduling", "bookingscheduling", "bookingscheduling", "index",array("id_space"), array(""));
        $this->addRoute("bookingschedulingedit", "bookingschedulingedit", "bookingscheduling", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("bookingaccessibilities", "bookingaccessibilities", "bookingaccessibilities", "index",array("id_space"), array(""));
        
        $this->addRoute("bookingdisplay", "bookingdisplay", "bookingdisplay", "index", array("id_space"), array(""));
        $this->addRoute("bookingdisplayedit", "bookingdisplayedit", "bookingdisplay", "edit", array("id_space", "id"), array("", ""));
        
        $this->addRoute("bookingpackages", "bookingpackages", "bookingpackages", "index", array("id_space"), array(""));
        $this->addRoute("bookingsupsinfo", "bookingsupsinfo", "bookingsupsinfo", "index", array("id_space"), array(""));
        $this->addRoute("bookingquantities", "bookingquantities", "bookingquantities", "index", array("id_space"), array(""));
        
        $this->addRoute("bookingnightwe", "bookingnightwe", "bookingnightwe", "index", array("id_space"), array(""));
        $this->addRoute("bookingnightweedit", "bookingnightweedit", "bookingnightwe", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("bookingnightweeditq", "bookingnightweeditq", "bookingnightwe", "editquery", array("id_space"), array(""));
        
        $this->addRoute("bookingcolorcodes", "bookingcolorcodes", "bookingcolorcodes", "index", array("id_space"), array(""));
        $this->addRoute("bookingcolorcodeedit", "bookingcolorcodeedit", "bookingcolorcodes", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("bookingcolorcodedelete", "bookingcolorcodedelete", "bookingcolorcodes", "delete", array("id_space", "id"), array("", ""));
        $this->addRoute("bookingblock", "bookingblock", "bookingblock", "index", array("id_space"), array(""));
        $this->addRoute("bookingblockquery", "bookingblockquery", "bookingblock", "blockresourcesquery", array("id_space"), array(""));
        
        $this->addRoute("bookingrestrictions", "bookingrestrictions", "bookingrestrictions", "index", array("id_space"), array(""));
        $this->addRoute("bookingrestrictionedit", "bookingrestrictionedit", "bookingrestrictions", "edit", array("id_space", "id"), array("", ""));
        
        $this->addRoute("bookingday", "bookingday", "booking", "day", array("id_space", "action", "message"), array("", "", ""));
        $this->addRoute("bookingdayarea", "bookingdayarea", "booking", "dayarea", array("id_space","action", "message"), array("", "", ""));
        $this->addRoute("bookingweek", "bookingweek", "booking", "week", array("id_space", "action", "message"), array("", "", ""));
        $this->addRoute("bookingweekarea", "bookingweekarea", "booking", "weekarea", array("id_space","action", "message"), array("", "", ""));
        $this->addRoute("bookingmonth", "bookingmonth", "booking", "month", array("id_space", "action", "message"), array("", "", ""));
        $this->addRoute("bookingeditreservation", "bookingeditreservation", "booking", "editreservation", array("id_space", "param"), array("", ""));
        
        $this->addRoute("bookingeditreservationquery", "bookingeditreservationquery", "bookingdefault", "editreservationquery", array("id_space"), array(""));
        $this->addRoute("bookingeditreservationdefaultdelete", "bookingeditreservationdefaultdelete", "bookingdefault", "delete", array("id_space", "id"), array("", ""));
        
        $this->addRoute("bookingeditreservationperiodicdelete", "bookingeditreservationperiodicdelete", "bookingdefault", "deleteperiod", array("id_space", "id_period"), array("", ""));
        
        $this->addRoute("bookingauthorisations", "bookingauthorisations", "bookingauthorisations", "index", array("id_space", "id_user"), array("", ""));
        $this->addRoute("bookingauthorisationshist", "bookingauthorisationshist", "bookingauthorisations", "history", array("id_space", "id"), array("", ""));
        $this->addRoute("bookingauthorisationsadd", "bookingauthorisationsadd", "bookingauthorisations", "add", array("id_space", "id"), array("", ""));
        $this->addRoute("bookingauthorisationsedit", "bookingauthorisationsedit", "bookingauthorisations", "edit", array("id_space", "id"), array("", ""));
        
        
        $this->addRoute("bookingprices", "bookingprices", "bookingprices", "index", array("id_space"), array(""));
        
        // @deprecated
        //$this->addRoute("bookingpricesowner", "bookingpricesowner", "bookingprices", "owner", array("id_space"), array(""));
        $this->addRoute("bookinginvoice", "bookinginvoice", "bookinginvoice", "index", array("id_space"), array(""));
        $this->addRoute("bookinginvoiceedit", "bookinginvoiceedit", "bookinginvoice", "edit", array("id_space", "id_invoice", "pdf"), array("", "", ""));
        $this->addRoute("bookinginvoicedetail", "bookinginvoicedetail", "bookinginvoice", "details", array("id_space", "id_invoice"), array("", ""));
        
        $this->addRoute("bookinggetprices", "bookinggetprices", "bookingprices", "getprices", array("id_space", "id_resource"), array("", ""), true);
        $this->addRoute("bookingpriceseditquery", "bookingpriceseditquery", "bookingprices", "editquery", array("id_space"), array(""));
        
        // statistics
        $this->addRoute("bookingstatisticauthorizations", "bookingstatisticauthorizations", "bookingstatisticauthorizations", "index", array("id_space"), array(""));
        $this->addRoute("bookingauthorizedusers", "bookingauthorizedusers", "bookingstatisticauthorizations", "authorizedusers", array("id_space"), array(""));
        $this->addRoute("bookingauthorizedusersquery", "bookingauthorizedusersquery", "bookingstatisticauthorizations", "authorizedusersquery", array("id_space"), array(""));
     
        $this->addRoute("bookingusersstats", "bookingusersstats", "bookingstatistics", "statbookingusers", array("id_space"), array(""));
        $this->addRoute("bookingreservationstats", "bookingreservationstats", "bookingstatistics", "statreservations", array("id_space"), array(""));
        $this->addRoute("bookingreservationstatsquery", "bookingreservationstatsquery", "bookingstatistics", "statreservationsquery", array("id_space"), array(""));
        $this->addRoute("bookinggrrstats", "bookinggrrstats", "bookingstatistics", "grr", array("id_space"), array(""));
        $this->addRoute("bookingstatquantities", "statquantities", "bookingstatistics", "statquantities", array("id_space"), array(""));
        $this->addRoute("bookingstatreservationresp", "bookingstatreservationresp", "bookingstatistics", "statreservationresp", array("id_space"), array(""));
    
        // update user resp in booking
        $this->addRoute("updateresaresps", "updateresaresps", "bookinginvoice", "updateresaresponsibles", array(), array());
           
    }
}

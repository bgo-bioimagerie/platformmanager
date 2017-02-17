<?php

require_once 'Framework/Routing.php';

class BookingRouting extends Routing{
   
    
    public function listRouts(){
        
        // config
        $this->addRoute("bookingconfig", "bookingconfig", "bookingconfig", "index", array("id_space"), array(""));
        $this->addRoute("bookingconfigadmin", "bookingconfigadmin", "bookingconfigadmin", "index");
        
        // user srttings
        $this->addRoute("bookingusersettings", "bookingusersettings", "bookingusersettings", "index");
        
        // add here the module routes
        $this->addRoute("booking", "booking", "booking", "index", array("is_space"), array(""));
        
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
        
        $this->addRoute("bookingday", "bookingday", "booking", "day", array("id_space", "action", "message"), array("", "", ""));
        $this->addRoute("bookingdayarea", "bookingdayarea", "booking", "dayarea", array("id_space","action", "message"), array("", "", ""));
        $this->addRoute("bookingweek", "bookingweek", "booking", "week", array("id_space", "action", "message"), array("", "", ""));
        $this->addRoute("bookingweekarea", "bookingweekarea", "booking", "weekarea", array("id_space","action", "message"), array("", "", ""));
        $this->addRoute("bookingmonth", "bookingmonth", "booking", "month", array("id_space", "action", "message"), array("", "", ""));
        $this->addRoute("bookingeditreservation", "bookingeditreservation", "booking", "editreservation", array("id_space", "param"), array("", ""));
        
        $this->addRoute("bookingeditreservationquery", "bookingeditreservationquery", "bookingdefault", "editreservationquery", array("id_space"), array(""));
        $this->addRoute("bookingeditreservationdefaultdelete", "bookingeditreservationdefaultdelete", "bookingdefault", "delete", array("id_space", "id"), array("", ""));
        
        $this->addRoute("bookingauthorisations", "bookingauthorisations", "bookingauthorisations", "index", array("id_space", "id"), array("", ""));
        $this->addRoute("bookingauthorisationsquery", "bookingauthorisationsquery", "bookingauthorisations", "query", array("id_space"), array(""));
        
        $this->addRoute("bookingprices", "bookingprices", "bookingprices", "index", array("id_space"), array(""));
        $this->addRoute("bookingpricesowner", "bookingpricesowner", "bookingprices", "owner", array("id_space"), array(""));
        $this->addRoute("bookinginvoice", "bookinginvoice", "bookinginvoice", "index", array("id_space"), array(""));
        $this->addRoute("bookinginvoiceedit", "bookinginvoiceedit", "bookinginvoice", "edit", array("id_space", "id_invoice", "pdf"), array("", "", ""));
        $this->addRoute("bookinginvoicedetail", "bookinginvoicedetail", "bookinginvoice", "details", array("id_space", "id_invoice"), array("", ""));
        
        // statistics
        $this->addRoute("bookingstatisticauthorizations", "bookingstatisticauthorizations", "bookingstatisticauthorizations", "index", array("id_space"), array(""));
        $this->addRoute("bookingauthorizedusers", "bookingauthorizedusers", "bookingstatisticauthorizations", "authorizedusers", array("id_space"), array(""));
        $this->addRoute("bookingauthorizedusersquery", "bookingauthorizedusersquery", "bookingstatisticauthorizations", "authorizedusersquery", array("id_space"), array(""));
     
        $this->addRoute("bookingusersstats", "bookingusersstats", "bookingstatistics", "statbookingusers", array("id_space"), array(""));
        $this->addRoute("bookingreservationstats", "bookingreservationstats", "bookingstatistics", "statreservations", array("id_space"), array(""));
        $this->addRoute("bookingreservationstatsquery", "bookingreservationstatsquery", "bookingstatistics", "statreservationsquery", array("id_space"), array(""));
        $this->addRoute("bookinggrrstats", "bookinggrrstats", "bookingstatistics", "grr", array("id_space"), array(""));
    
        
        
    }
}

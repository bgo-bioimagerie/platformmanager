<?php

require_once 'Framework/Routing.php';

class BookingRouting extends Routing{
    
    public function listRouts(){
        
        // config
        $this->addRoute("bookingconfig", "bookingconfig", "bookingconfig", "index");
        
        // add here the module routes
        $this->addRoute("booking", "booking", "booking", "index");
        
        $this->addRoute("bookingsettings", "bookingsettings", "bookingsettings", "index");
        $this->addRoute("bookingscheduling", "bookingscheduling", "bookingscheduling", "index");
        $this->addRoute("bookingschedulingedit", "bookingschedulingedit", "bookingscheduling", "edit", array("id"), array(""));
        $this->addRoute("bookingaccessibilities", "bookingaccessibilities", "bookingaccessibilities", "index");
        
        $this->addRoute("bookingdisplay", "bookingdisplay", "bookingdisplay", "index");
        $this->addRoute("bookingdisplayedit", "bookingdisplayedit", "bookingdisplay", "edit", array("id"), array(""));
        
        $this->addRoute("bookingpackages", "bookingpackages", "bookingpackages", "index");
        $this->addRoute("bookingsupsinfo", "bookingsupsinfo", "bookingsupsinfo", "index");
        $this->addRoute("bookingquantities", "bookingquantities", "bookingquantities", "index");
        
        $this->addRoute("bookingcolorcodes", "bookingcolorcodes", "bookingcolorcodes", "index");
        $this->addRoute("bookingcolorcodeedit", "bookingcolorcodeedit", "bookingcolorcodes", "edit", array("id"), array(""));
        $this->addRoute("bookingcolorcodedelete", "bookingcolorcodedelete", "bookingcolorcodes", "delete", array("id"), array(""));
        $this->addRoute("bookingblock", "bookingblock", "bookingblock", "index");
        
        $this->addRoute("bookingday", "bookingday", "booking", "day", array("action", "message"), array("", ""));
        $this->addRoute("bookingdayarea", "bookingdayarea", "booking", "dayarea", array("action", "message"), array("", ""));
        $this->addRoute("bookingweek", "bookingweek", "booking", "week", array("action", "message"), array("", ""));
        $this->addRoute("bookingweekarea", "bookingweekarea", "booking", "weekarea", array("action", "message"), array("", ""));
        $this->addRoute("bookingmonth", "bookingmonth", "booking", "month", array("action", "message"), array("", ""));
        $this->addRoute("bookingeditreservation", "bookingeditreservation", "booking", "editreservation", array("param"), array(""));
        $this->addRoute("bookingeditreservationquery", "bookingeditreservationquery", "booking", "editreservationquery");
        
        
        
        
        
        
    }
}

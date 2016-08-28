<?php

require_once 'Framework/Routing.php';

class BookingStatistics extends Routing{
    
    public function listRouts(){
        
        // statistics
        $this->addRoute("bookingstatisticauthorizations", "bookingstatisticauthorizations", "bookingstatisticauthorizations", "index", array("id_space"), array(""));
        $this->addRoute("bookingauthorizedusers", "bookingauthorizedusers", "bookingstatisticauthorizations", "authorizedusers", array("id_space"), array(""));
        $this->addRoute("bookingusersstats", "bookingusersstats", "bookingstatistics", "statbookingusers", array("id_space"), array(""));
        $this->addRoute("bookingreservationstats", "bookingreservationstats", "bookingstatistics", "statreservations", array("id_space"), array(""));
        $this->addRoute("bookinggrrstats", "bookinggrrstats", "bookingstatistics", "grr", array("id_space"), array(""));
    
    }
}

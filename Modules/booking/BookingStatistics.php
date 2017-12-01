<?php

require_once 'Framework/Routing.php';

class BookingStatistics extends Routing{
    
        private $id_space;

    public function setSpace($id_space) {
        $this->id_space = $id_space;
    }
    
    public function listRouts(){
        
        // statistics
        $this->addRoute("bookingstatisticauthorizations", "bookingstatisticauthorizations", "bookingstatisticauthorizations", "index", array("id_space"), array(""));
        $this->addRoute("bookingauthorizedusers", "bookingauthorizedusers", "bookingstatisticauthorizations", "authorizedusers", array("id_space"), array(""));
        $this->addRoute("bookingusersstats", "bookingusersstats", "bookingstatistics", "statbookingusers", array("id_space"), array(""));
        $this->addRoute("bookingreservationstats", "bookingreservationstats", "bookingstatistics", "statreservations", array("id_space"), array(""));
        $this->addRoute("bookinggrrstats", "bookinggrrstats", "bookingstatistics", "grr", array("id_space"), array(""));
        $this->addRoute("bookingstatquantities", "statquantities", "bookingstatistics", "statquantities", array("id_space"), array(""));
    
    }
}

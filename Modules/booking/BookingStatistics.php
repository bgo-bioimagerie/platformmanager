<?php

require_once 'Framework/Routing.php';

class BookingStatistics extends Routing
{
    private $idSpace;

    public function setSpace($idSpace)
    {
        $this->id_space = $idSpace;
    }

    public function listRoutes()
    {
        // statistics
        $this->addRoute("bookingstatisticauthorizations", "bookingstatisticauthorizations", "bookingstatisticauthorizations", "index", array("id_space"), array(""));
        $this->addRoute("bookingauthorizedusers", "bookingauthorizedusers", "bookingstatisticauthorizations", "authorizedusers", array("id_space"), array(""));
        $this->addRoute("bookingusersstats", "bookingusersstats", "bookingstatistics", "statbookingusers", array("id_space"), array(""));
        $this->addRoute("bookingreservationstats", "bookingreservationstats", "bookingstatistics", "statreservations", array("id_space"), array(""));
        $this->addRoute("bookinggrrstats", "bookinggrrstats", "bookingstatistics", "grr", array("id_space"), array(""));
        $this->addRoute("bookingstatquantities", "statquantities", "bookingstatistics", "statquantities", array("id_space"), array(""));
        $this->addRoute("bookingstatreservationresp", "bookingstatreservationresp", "bookingstatistics", "statreservationresp", array("id_space"), array(""));
    }
}

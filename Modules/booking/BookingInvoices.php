<?php

require_once 'Framework/Routing.php';

class BookingInvoices extends Routing
{
    private $idSpace;

    public function setSpace($idSpace)
    {
        $this->id_space = $idSpace;
    }

    public function listRoutes()
    {
        // statistics
        $this->addRoute("bookinginvoice", "bookinginvoice", "bookinginvoice", "index", array("id_space"), array(""));
        $this->addRoute("bookingprices", "bookingprices", "bookingprices", "index", array("id_space"), array(""));
    }
}

<?php

require_once 'Framework/Routing.php';

class BookingInvoices extends Routing {

    private $id_space;

    public function setSpace($id_space) {
        $this->id_space = $id_space;
    }

    public function listRouts() {
        // statistics
        $this->addRoute("bookinginvoice", "bookinginvoice", "bookinginvoice", "index", array("id_space"), array(""));
        $this->addRoute("bookingprices", "bookingprices", "bookingprices", "index", array("id_space"), array(""));
    }

}

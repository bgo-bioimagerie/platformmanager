<?php

require_once 'Framework/Routing.php';

class BookingInvoices extends Routing{
    
    public function listRouts(){
        
        // statistics
        $this->addRoute("bookingprices", "bookingprices", "bookingprices", "index", array("id_space"), array(""));
        $this->addRoute("bookinginvoice", "bookinginvoice", "bookinginvoice", "index", array("id_space"), array(""));
        
    }
}

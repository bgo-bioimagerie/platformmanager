<?php

require_once 'Framework/Routing.php';

class QuoteRouting extends Routing
{
    public function routes($router)
    {
        $router->map('GET|POST', '/quote/[i:id_space]/pdftemplate', 'quote/quoteconfig/pdftemplate', 'quote_pdftemplate');
        $router->map('GET|POST', '/quote/[i:id_space]/pdftemplatedelete/[:name]', 'quote/quoteconfig/pdftemplatedelete', 'quote_pdftemplate_delete');
    }

    public function listRoutes()
    {
        // config
        $this->addRoute("quoteconfig", "quoteconfig", "quoteconfig", "index", array("id_space"), array(""));


        // add here the module routes
        $this->addRoute("quote", "quote", "quotelist", "index", array("id_space"), array(""));
        $this->addRoute("quoteedit", "quoteedit", "quotelist", "edit", array("id_space", "id"), array("", ""));

        $this->addRoute("quoteuser", "quoteuser", "quotelist", "editexistinguser", array("id_space", "id"), array("", ""));
        $this->addRoute("quotenew", "quotenew", "quotelist", "editnewuser", array("id_space", "id"), array("", ""));

        $this->addRoute("quotegetitem", "quotegetitem", "quote", "getitem", array("id_space", "id"), array("", ""), true);

        $this->addRoute("quoteedititem", "quoteedititem", "quotelist", "edititem", array("id_space"), array(""));

        $this->addRoute("quotedelete", "quotedelete", "quotelist", "delete", array("id_space", "id"), array("", ""));

        $this->addRoute("quotepdf", "quotepdf", "quotelist", "pdf", array("id_space", "id"), array("", ""));
    }
}

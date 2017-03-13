<?php

require_once 'Framework/Model.php';

require_once 'Modules/quote/Model/Quote.php';
require_once 'Modules/quote/Model/QuoteItem.php';

/**
 * Class defining methods to install and initialize the core database
 *
 * @author Sylvain Prigent
 */
class QuoteInstall extends Model {

    /**
     * Create the core database
     *
     * @return boolean True if the base is created successfully
     */
    public function createDatabase() {        

        $quote = new Quote();
        $quote->createTable();
        
        $quoteitem = new QuoteItem();
        $quoteitem->createTable();
        
        if (!file_exists('data/quote/')) {
            mkdir('data/quote/', 0777, true);
        }
    }
}

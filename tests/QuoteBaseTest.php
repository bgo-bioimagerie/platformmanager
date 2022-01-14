<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/quote/Controller/QuoteconfigController.php';
require_once 'Modules/quote/Controller/QuotelistController.php';


require_once 'tests/BaseTest.php';

class QuoteBaseTest extends BaseTest {

    protected function activateQuote($space, $user) {
        Configuration::getLogger()->debug('activate quotes', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        $req = $this->request([
            "path" => "quoteconfig/".$space['id'],
            "formid" => "quotemenusactivationForm",
            "quoteMenustatus" => 3,
            "quoteDisplayMenu" => 0,
            "quoteDisplayColor" =>  "#000000",
            "quoteDisplayColorTxt" => "#ffffff"
        ]);
        $c = new QuoteconfigController($req, $space);
        $c->runAction('quote', 'index', ['id_space' => $space['id']]);
        $req = $this->request(["path" => "corespace/".$space['id']]);
        $c = new CorespaceController($req, $space);
        $spaceView = $c->runAction('core', 'view', ['id_space' => $space['id']]);
        $quoteEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'quote') {
                $quoteEnabled = true;
            }
        }
        $this->assertTrue($quoteEnabled);

    }

    protected function createQuoteUser($space, $user, $client):int {
        $req = $this->request([
            "path" => "quoteuser/".$space['id'],
            "formid" => "editexistinguserForm",
            "id_space" => $space['id'],
            "id_user" => $user['id'],
            "id_client" => $client['id'],
            'date_open' => date('Y-m-d')
        ]);
        $c = new QuotelistController($req, $space);
        $data = $c->runAction('quote', 'editexistinguser', ['id_space' => $space['id'], 'id' => 0]);
        $this->assertTrue($data['quote']['id'] > 0);
        return $data['quote']['id'];
    }

    protected function getQuote($space, $id):array {
        $req = $this->request([
            "path" => "quoteuser/".$space['id'],
        ]);
        $c = new QuotelistController($req, $space);
        return $c->runAction('quote', 'edit', ['id_space' => $space['id'], 'id' => $id]);
    }

    protected function addQuoteItem($space, $quote, $item): int{
        $req = $this->request([
            "path" => "quoteedititem/".$space['id'],
            "formid" => "createItemForm",
            "id" => 0,
            "id_quote" => $quote['id'],
            "id_item" => $item['id'],
            "quantity" => $item['quantity'],
            "comment" => $item['comment']
        ]);
        $c = new QuotelistController($req, $space);
        $data = $c->runAction('quote', 'edititem', ['id_space' => $space['id']]);
        $this->assertTrue($data['item']['id'] > 0);
        return $data['item']['id'];        
    }

}


?>
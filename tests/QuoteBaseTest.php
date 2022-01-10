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
        $req = new Request([
            "path" => "quoteconfig/".$space['id'],
            "formid" => "quotemenusactivationForm",
            "quoteMenustatus" => 3,
            "quoteDisplayMenu" => 0,
            "quoteDisplayColor" =>  "#000000",
            "quoteDisplayColorTxt" => "#ffffff"
        ], false);
        $c = new QuoteconfigController($req, $space);
        $c->indexAction($space['id']);
        $c = new CorespaceController(new Request(["path" => "corespace/".$space['id']], false), $space);
        $spaceView = $c->viewAction($space['id']);
        $quoteEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'quote') {
                $quoteEnabled = true;
            }
        }
        $this->assertTrue($quoteEnabled);

    }

    protected function createQuoteUser($space, $user, $client) {
        $req = new Request([
            "path" => "quoteuser/".$space['id'],
            "formid" => "editexistinguserForm",
            "id_space" => $space['id'],
            "id_user" => $user['id'],
            "id_client" => $client['id'],
            'date_open' => date('Y-m-d')
        ], false);
        $c = new QuotelistController($req, $space);
        $data = $c->editexistinguserAction($space['id'], 0);
        $this->assertTrue($data['quote']['id'] > 0);
        return $data['quote']['id'];

    }

}


?>
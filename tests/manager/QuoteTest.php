<?php

require_once 'tests/QuoteBaseTest.php';
require_once 'Modules/clients/Controller/ClientslistController.php';
require_once 'Modules/services/Controller/ServiceslistingController.php';

class QuoteTest extends QuoteBaseTest {

    public function testConfigureModuleQuote() {
        $ctx = $this->Context();
        $spaces = $ctx['spaces'];
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            if(empty($data['admins'])) {
                continue;
            }
            $user = $this->user($data['admins'][0]);
            $this->activateQuote($space, $user);
        }
    }

    public function testCreateQuote() {
        $ctx = $this->Context();
        $spaces = array_keys($ctx['spaces']);
        $space = $this->space($spaces[0]);
        $manager = $this->user($ctx['spaces'][$spaces[0]]['managers'][0]);
        $user = $this->user($ctx['spaces'][$spaces[0]]['users'][0]);
        $this->asUser($manager['login'], $space['id']);
        $req = new Request([
            "path" => "clclients/".$space['id'],
            "id" => 0
         ], false); 
        $c = new ClientslistController($req, $space);
        $data = $c->indexAction($space['id']);
        $clients = $data['clients'];
        $client = $clients[0];

        // User cannot create quote
        $canQuote = true;
        try {
            $this->asUser($user['login'], $space['id']);
            $this->createQuoteUser($space, $user, $client);
        } catch(Exception) {
            $canQuote = false;
        }
        $this->assertFalse($canQuote);

        $this->asUser($manager['login'], $space['id']);
        $id_quote = $this->createQuoteUser($space, $user, $client);

        $req = new Request([
            "path" => "services/".$space['id'],

        ], false);
        $c = new ServiceslistingController($req, $space);
        $data = $c->listingAction($space['id']);
        $services = $data['services'];
        foreach ($services as $service) {
            $this->addQuoteItem($space, ['id' => $id_quote], ['id' => 'services_'.$service['id'] , 'quantity' => 1, 'comment' => $service['name']]);
        }

    }

}

?>
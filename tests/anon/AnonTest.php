<?php

require_once 'tests/BaseTest.php';

require_once 'Modules/core/Controller/CoreaboutController.php';
require_once 'Modules/core/Controller/CorespaceController.php';

class Anonest extends BaseTest {

    public function testAnonAccess(){
        $ctx = $this->Context();
        $this->asAnon();
        $spaces = $ctx['spaces'];
        $req = $this->request([
            "path" => "core/about",
         ]); 
        $c = new CoreaboutController($req);
        $data = $c->runAction('core', 'index', []);
        $this->assertTrue($data['tag'] != '');
        foreach($spaces as $spaceName => $data) {
            $space = $this->space($spaceName);
            $req = $this->request([
                "path" => "core/about",
             ]); 
            $c = new CorespaceController($req, $space);
            $data = $c->runAction('core', 'view', ['id_space' => $space['id']]);
            $this->assertTrue(empty($data['spaceMenuItems']));
        }
    }
}

?>
<?php
require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/resources/Controller/ResourcesconfigController.php';
require_once 'Modules/resources/Controller/ReareasController.php';
require_once 'Modules/resources/Controller/RecategoriesController.php';
require_once 'Modules/resources/Controller/RevisasController.php';
require_once 'Modules/resources/Controller/ResourcesinfoController.php';
require_once 'Modules/resources/Controller/ResourcesController.php';

require_once 'tests/BaseTest.php';

class ResourcesBaseTest extends BaseTest {


    protected function activateResources($space, $user) {
        Configuration::getLogger()->debug('setup resources', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        // activate resources module
        $req = new Request([
            "path" => "resourcesconfig/".$space['id'],
            "formid" => "menusactivationForm",
            "resourcesmenustatus" => 3,
            "displayMenu" => 0,
            "displayColor" =>  "#000000",
            "displayTxtColor" => "#ffffff"
        ], true);
        $c = new ResourcesconfigController($req, $space);
        $c->indexAction($space['id']);
        $c = new CorespaceController(new Request(["path" => "corespace/".$space['id']], false), $space);
        $spaceView = $c->viewAction($space['id']);
        $resourcesEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'resources') {
                $resourcesEnabled = true;
            }
        }
        $this->assertTrue($resourcesEnabled);
    }

    protected function setupResources($space, $user, $suffix='') {
        Configuration::getLogger()->debug('setup resources', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        // Create area
        $req = new Request([
            "path" => "reareasedit/".$space['id']."/0",
            "formid" => "reareasedit/".$space['id'],
            "id" => 0,
            "name" => "rearea1".$suffix,
            "is_restricted" => 0
        ], false);
        $c = new ReareasController($req, $space);
        $c->editAction($space['id'], 0);
        $data = $c->indexAction($space['id']);
        $reareas = $data['reareas'];
        $this->assertFalse(empty($reareas));
        // Create category
        $req = new Request([
            "path" => "recategoriesedit/".$space['id']."/0",
            "formid" => "recategoriesedit/".$space['id'],
            "id" => 0,
            "name" => "cat1".$suffix,
        ], false);
        $c = new RecategoriesController($req, $space);
        $c->editAction($space['id'], 0);
        $data = $c->indexAction($space['id']);
        $categories = $data['recategories'];
        $this->assertFalse(empty($categories));

        // Create visa
        $req = new Request([
            "path" => "resourceseditvisa/".$space['id']."/0",
            "formid" => "formeditVisa",
            "id" => 0,
            "id_resource_category" => $categories[0]['id'],
            "id_instructor" => $user['id'],
            "is_active" => 1,
            "instructor_status" => 1
        ], false);
        $c = new RevisasController($req, $space);
        $visa = $c->editAction($space['id'], 0);
        $this->assertTrue($visa['revisa']['id'] > 0);
        $data = $c->indexAction($space['id']);
        $visas = $data['revisas'];
        $this->assertFalse(empty($visas));    
        // Create 3 resource
        for($i=0;$i<3;$i++) {
            $req = new Request([
                "path" => "resourcesedit/".$space['id']."/0",
                "formid" => "resourcesedit",
                "id" => 0,
                "name" => "res".$i.$suffix,
                "brand" => "",
                "type" => "",
                "description" => "testing",
                "long_description" => "long testing",
                "id_category" => $categories[0]['id'],
                "id_area" => $reareas[0]['id'],
                "display_order" => 0
            ], false);
            $c = new ResourcesinfoController($req, $space);
            $resource = $c->editAction($space['id'], 0);
            $this->assertTrue($resource['resource']['id'] > 0);
        }
        $data = $c->indexAction($space['id']);
        $resources = $data['resources'];
        $this->assertFalse(empty($resources));
        $this->assertEquals(3, count($resources));
    }


}


?>
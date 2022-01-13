<?php
require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/resources/Controller/ResourcesconfigController.php';
require_once 'Modules/resources/Controller/ReareasController.php';
require_once 'Modules/resources/Controller/RecategoriesController.php';
require_once 'Modules/resources/Controller/RerespsstatusController.php';
require_once 'Modules/resources/Controller/RestatesController.php';
require_once 'Modules/resources/Controller/ReeventtypesController.php';

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
            "formid" => "resourcesmenusactivationForm",
            "resourcesMenustatus" => 3,
            "resourcesDisplayMenu" => 0,
            "resourcesDisplayColor" =>  "#000000",
            "resourcesDisplayColorTxt" => "#ffffff"
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

    protected function getResources($space) {
        $req = new Request([
            "path" => "resources/".$space['id']
        ], false);
        $c = new ResourcesinfoController($req, $space);
        $data = $c->indexAction($space['id']);
        return $data['resources'];        
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


    protected function addInstructorStatus($space, $name){
        $req = new Request([
            "path" => "rerespsstatusedit/".$space['id']."/0",
            "formid" => "rerespsstatusedit",
            "id" => 0,
            "name" => $name
        ], false);
        $c = new RerespsstatusController($req, $space);
        $status = $c->editAction($space['id'], 0);
        $this->assertTrue($status['rerespsstatus']['id'] > 0);
        return $status['rerespsstatus'];
    }

    protected function addEventType($space, $name){
        $req = new Request([
            "path" => "reeventtypesedit/".$space['id']."/0",
            "formid" => "reeventtypesedit",
            "id" => 0,
            "name" => $name
        ], false);
        $c = new ReeventtypesController($req, $space);
        $status = $c->editAction($space['id'], 0);
        $this->assertTrue($status['reeventtype']['id'] > 0);
        return $status['reeventtype'];
    }

    protected function addState($space, $name){
        $req = new Request([
            "path" => "restatesedit/".$space['id']."/0",
            "formid" => "restatesedit",
            "id" => 0,
            "name" => $name,
            "color" => '#ff0000'
        ], false);
        $c = new RestatesController($req, $space);
        $status = $c->editAction($space['id'], 0);
        $this->assertTrue($status['restate']['id'] > 0);
        return $status['restate'];
    }

    protected function addResourceEvent($space, $resource, $user, $eventtype, $state) {
        $req = new Request([
            "path" => "resourceeditevent/".$space['id']."/".$resource['id']."/0",
            "formid" => "editevent",
            "date" => date('Y-m-d'),
            "id_user" => $user['id'],
            "id_eventtype" => $eventtype['id'],
            "id_state" => $state['id'],
            "comment" => "some event at ".time()
        ], false);
        $c = new ResourcesinfoController($req, $space);
        $status = $c->editeventAction($space['id'], $resource['id'], 0);
        $this->assertTrue($status['reevent']['id'] > 0);
        return $status['reevent'];     
    }
}


?>
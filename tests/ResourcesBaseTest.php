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
        $req = $this->request([
            "path" => "resourcesconfig/".$space['id'],
            "formid" => "resourcesmenusactivationForm",
            "resourcesMenustatus" => 3,
            "resourcesDisplayMenu" => 0,
            "resourcesDisplayColor" =>  "#000000",
            "resourcesDisplayColorTxt" => "#ffffff"
        ]);
        $c = new ResourcesconfigController($req, $space);
        $c->runAction('resources', 'index', ['id_space' => $space['id']]);
        $req = $this->request(["path" => "corespace/".$space['id']]);
        $c = new CorespaceController($req, $space);
        $spaceView = $c->runAction('core', 'view', ['id_space' => $space['id']]);
        $resourcesEnabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'resources') {
                $resourcesEnabled = true;
            }
        }
        $this->assertTrue($resourcesEnabled);
    }

    protected function getResources($space) {
        $req = $this->request([
            "path" => "resources/".$space['id']
        ]);
        $c = new ResourcesinfoController($req, $space);
        $data = $c->runAction('resources', 'index', ['id_space' => $space['id']]);
        return $data['resources'];        
    }

    protected function setupResources($space, $user, $suffix='') {
        Configuration::getLogger()->debug('setup resources', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        // Create area
        $req = $this->request([
            "path" => "reareasedit/".$space['id']."/0",
            "formid" => "reareasedit/".$space['id'],
            "id" => 0,
            "name" => "rearea1".$suffix,
            "is_restricted" => 0
        ]);
        $c = new ReareasController($req, $space);
        $c->runAction('resources', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $data = $c->runAction('resources', 'index', ['id_space' => $space['id']]);
        $reareas = $data['reareas'];
        $this->assertFalse(empty($reareas));
        // Create category
        $req = $this->request([
            "path" => "recategoriesedit/".$space['id']."/0",
            "formid" => "recategoriesedit/".$space['id'],
            "id" => 0,
            "name" => "cat1".$suffix,
        ]);
        $c = new RecategoriesController($req, $space);
        $c->runAction('resources', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $data = $c->runAction('resources', 'index', ['id_space' => $space['id']]);
        $categories = $data['recategories'];
        $this->assertFalse(empty($categories));

        // Create visa
        $req = $this->request([
            "path" => "resourceseditvisa/".$space['id']."/0",
            "formid" => "formeditVisa",
            "id" => 0,
            "id_resource_category" => $categories[0]['id'],
            "id_instructor" => $user['id'],
            "is_active" => 1,
            "instructor_status" => 1
        ]);
        $c = new RevisasController($req, $space);
        $visa = $c->runAction('resources', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $this->assertTrue($visa['revisa']['id'] > 0);
        $data = $c->runAction('resources', 'index', ['id_space' => $space['id']]);
        $visas = $data['revisas'];
        $this->assertFalse(empty($visas));    
        // Create 3 resource
        for($i=0;$i<3;$i++) {
            $req = $this->request([
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
            ]);
            $c = new ResourcesinfoController($req, $space);
            $resource = $c->runAction('resources', 'edit', ['id_space' => $space['id'], 'id' => 0]);
            $this->assertTrue($resource['resource']['id'] > 0);
        }
        $data = $c->runAction('resources', 'index', ['id_space' => $space['id']]);
        $resources = $data['resources'];
        $this->assertFalse(empty($resources));
        $this->assertEquals(3, count($resources));
    }


    protected function addInstructorStatus($space, $name){
        $req = $this->request([
            "path" => "rerespsstatusedit/".$space['id']."/0",
            "formid" => "rerespsstatusedit",
            "id" => 0,
            "name" => $name
        ]);
        $c = new RerespsstatusController($req, $space);
        $status = $c->runAction('resources', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $this->assertTrue($status['rerespsstatus']['id'] > 0);
        return $status['rerespsstatus'];
    }

    protected function addEventType($space, $name){
        $req = $this->request([
            "path" => "reeventtypesedit/".$space['id']."/0",
            "formid" => "reeventtypesedit",
            "id" => 0,
            "name" => $name
        ]);
        $c = new ReeventtypesController($req, $space);
        $status = $c->runAction('resources', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $this->assertTrue($status['reeventtype']['id'] > 0);
        return $status['reeventtype'];
    }

    protected function addState($space, $name){
        $req = $this->request([
            "path" => "restatesedit/".$space['id']."/0",
            "formid" => "restatesedit",
            "id" => 0,
            "name" => $name,
            "color" => '#ff0000'
        ]);
        $c = new RestatesController($req, $space);
        $status = $c->runAction('resources', 'edit', ['id_space' => $space['id'], 'id' => 0]);
        $this->assertTrue($status['restate']['id'] > 0);
        return $status['restate'];
    }

    protected function addResourceEvent($space, $resource, $user, $eventtype, $state) {
        $req = $this->request([
            "path" => "resourceeditevent/".$space['id']."/".$resource['id']."/0",
            "formid" => "editevent",
            "date" => date('Y-m-d'),
            "id_user" => $user['id'],
            "id_eventtype" => $eventtype['id'],
            "id_state" => $state['id'],
            "comment" => "some event at ".time()
        ]);
        $c = new ResourcesinfoController($req, $space);
        $status = $c->runAction('resources', 'editevent', ['id_space' => $space['id'], 'id_resource' => $resource['id'], 'id_event' => 0]);
        $this->assertTrue($status['reevent']['id'] > 0);
        return $status['reevent'];     
    }
}


?>
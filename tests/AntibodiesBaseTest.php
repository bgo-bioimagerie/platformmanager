<?php

require './vendor/autoload.php';
require_once 'Modules/core/Controller/CoreconnectionController.php';
require_once 'Framework/Request.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/antibodies/Controller/AntibodiesconfigController.php';
require_once 'Modules/antibodies/Controller/StainingController.php';
require_once 'Modules/antibodies/Controller/ApplicationController.php';
require_once 'Modules/antibodies/Controller/AciiController.php';
require_once 'Modules/antibodies/Controller/IncController.php';
require_once 'Modules/antibodies/Controller/AciincController.php';
require_once 'Modules/antibodies/Controller/LinkerController.php';
require_once 'Modules/antibodies/Controller/DemController.php';
require_once 'Modules/antibodies/Controller/EnzymesController.php';
require_once 'Modules/antibodies/Controller/OptionController.php';
require_once 'Modules/antibodies/Controller/FixativeController.php';
require_once 'Modules/antibodies/Controller/ProtoController.php';
require_once 'Modules/antibodies/Controller/KitController.php';
require_once 'Modules/antibodies/Controller/StatusController.php';
require_once 'Modules/antibodies/Controller/PrelevementsController.php';
require_once 'Modules/antibodies/Controller/OrganesController.php';
require_once 'Modules/antibodies/Controller/EspecesController.php';
require_once 'Modules/antibodies/Controller/IsotypesController.php';
require_once 'Modules/antibodies/Controller/SourcesController.php';
require_once 'Modules/antibodies/Controller/ProtocolsController.php';
require_once 'Modules/antibodies/Controller/AntibodieslistController.php';


require_once 'tests/BaseTest.php';

class AntibodiesBaseTest extends BaseTest {

    protected function activateAntibodies($space, $user) {
        Configuration::getLogger()->debug('activate antibodies', ['user' => $user, 'space' => $space]);
        $this->asUser($user['login'], $space['id']);
        // activate booking module
        $req = $this->request([
            "path" => "antibodiesconfig/".$space['id'],
            "formid" => "antibodiesmenusactivationForm",
            "antibodiesMenustatus" => 2,
            "antibodiesDisplayMenu" => 0,
            "antibodiesDisplayColor" =>  "#000000",
            "antibodiesDisplayColorTxt" => "#ffffff"
        ]);
        $c = new AntibodiesconfigController($req, $space);
        $c->runAction('antibodies', 'index', ['id_space' => $space['id']]);

        $req = $this->request(["path" => "corespace/".$space['id']]);
        $c = new CorespaceController($req, $space);
        $spaceView = $c->runAction('core', 'view', ['id_space' => $space['id']]);
        $enabled = false;
        foreach($spaceView['spaceMenuItems'] as $menu) {
            if($menu['url'] == 'antibodies') {
                $enabled = true;
            }
        }
        $this->assertTrue($enabled);
    }

    protected function createAntibodies($space) {
        $req = $this->request([
            "path" => "sourcesedit/".$space['id']."/0",
            "formid" => "sourceseditform",
            "nom" => "source1",
            "id" => 0
        ]);
        $c = new SourcesController($req, $space);
        $data = $c->runAction('sources', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['source']['id'] > 0);

        $source = $data['source']['id'];


        $req = $this->request([
            "path" => "isotypesedit/".$space['id']."/0",
            "formid" => "isotypeseditform",
            "nom" => "isot1",
            "id" => 0
        ]);
        $c = new IsotypesController($req, $space);
        $data = $c->runAction('isotypes', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['isotype']['id'] > 0);

        $isotype = $data['isotype']['id'];


        $req = $this->request([
            "path" => "especesedit/".$space['id']."/0",
            "formid" => "especeseditform",
            "nom" => "espece1",
            "id" => 0
        ]);
        $c = new EspecesController($req, $space);
        $data = $c->runAction('especes', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['espece']['id'] > 0);

        $espece = $data['espece']['id'];


        $req = $this->request([
            "path" => "organessedit/".$space['id']."/0",
            "formid" => "organeseditform",
            "nom" => "org1",
            "id" => 0
        ]);
        $c = new OrganesController($req, $space);
        $data = $c->runAction('organes', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['organe']['id'] > 0);

        $organe = $data['organe']['id'];


        $req = $this->request([
            "path" => "prelevementsedit/".$space['id']."/0",
            "formid" => "prelevementseditform",
            "nom" => "prel1",
            "id" => 0
        ]);
        $c = new PrelevementsController($req, $space);
        $data = $c->runAction('prelevements', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['prelevement']['id'] > 0);

        $prelevement = $data['prelevement']['id'];

        
        $req = $this->request([
            "path" => "statusedit/".$space['id']."/0",
            "formid" => "statuseditform",
            "nom" => "status1",
            "color" => "#ffffff",
            "display_order" => 0,
            "id" => 0
        ]);
        $c = new StatusController($req, $space);
        $data = $c->runAction('status', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['status']['id'] > 0);
        $status = $data['status']['id'];


        $req = $this->request([
            "path" => "kitedit/".$space['id']."/0",
            "formid" => "kitseditform",
            "nom" => "kit1",
            "id" => 0
        ]);
        $c = new KitController($req, $space);
        $data = $c->runAction('kit', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['kit']['id'] > 0);
        $kit = $data['kit']['id'];


        $req = $this->request([
            "path" => "protoedit/".$space['id']."/0",
            "formid" => "protoeditform",
            "nom" => "proto1",
            "id" => 0
        ]);
        $c = new ProtoController($req, $space);
        $data = $c->runAction('proto', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['proto']['id'] > 0);
        $proto = $data['proto']['id'];


        $req = $this->request([
            "path" => "fixativeedit/".$space['id']."/0",
            "formid" => "fixativeeditform",
            "nom" => "fix1",
            "id" => 0
        ]);
        $c = new FixativeController($req, $space);
        $data = $c->runAction('fixative', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['fixative']['id'] > 0);
        $fixative = $data['fixative']['id'];


        $req = $this->request([
            "path" => "optionedit/".$space['id']."/0",
            "formid" => "optionseditform",
            "nom" => "opt1",
            "id" => 0
        ]);
        $c = new OptionController($req, $space);
        $data = $c->runAction('option', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['option']['id'] > 0);
        $option = $data['option']['id'];


        $req = $this->request([
            "path" => "enzymesedit/".$space['id']."/0",
            "formid" => "enzymeseditform",
            "nom" => "enz1",
            "id" => 0
        ]);
        $c = new EnzymesController($req, $space);
        $data = $c->runAction('enzymes', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['enzyme']['id'] > 0);
        $enzyme = $data['enzyme']['id'];


        $req = $this->request([
            "path" => "demcedit/".$space['id']."/0",
            "formid" => "demeditform",
            "nom" => "dem1",
            "id" => 0
        ]);
        $c = new DemController($req, $space);
        $data = $c->runAction('dem', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['dem']['id'] > 0);
        $dem = $data['dem']['id'];


        $req = $this->request([
            "path" => "aciincedit/".$space['id']."/0",
            "formid" => "aciinceditform",
            "nom" => "aciinc1",
            "id" => 0
        ]);
        $c = new AciincController($req, $space);
        $data = $c->runAction('aciinc', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['aciinc']['id'] > 0);
        $aciinc = $data['aciinc']['id'];


        $req = $this->request([
            "path" => "linkeredit/".$space['id']."/0",
            "formid" => "linkereditform",
            "nom" => "link1",
            "id" => 0
        ]);
        $c = new LinkerController($req, $space);
        $data = $c->runAction('linker', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['linker']['id'] > 0);
        $linker = $data['linker']['id'];



        $req = $this->request([
            "path" => "incedit/".$space['id']."/0",
            "formid" => "incseditform",
            "nom" => "inc1",
            "id" => 0
        ]);
        $c = new IncController($req, $space);
        $data = $c->runAction('inc', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['inc']['id'] > 0);
        $inc = $data['inc']['id'];


        $req = $this->request([
            "path" => "aciiedit/".$space['id']."/0",
            "formid" => "aciieditform",
            "nom" => "acii1",
            "id" => 0
        ]);
        $c = new AciiController($req, $space);
        $data = $c->runAction('acii', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['acii']['id'] > 0);
        $acii = $data['acii']['id'];


        $req = $this->request([
            "path" => "applicationedit/".$space['id']."/0",
            "formid" => "acapplicationeditform",
            "nom" => "app1",
            "id" => 0
        ]);
        $c = new ApplicationController($req, $space);
        $data = $c->runAction('application', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['application']['id'] > 0);
        $application = $data['application']['id'];


        $req = $this->request([
            "path" => "stainingedit/".$space['id']."/0",
            "formid" => "stainingeditform",
            "nom" => "st1",
            "id" => 0
        ]);
        $c = new StainingController($req, $space);
        $data = $c->runAction('staining', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $this->assertTrue($data['staining']['id'] > 0);
        $staining = $data['staining']['id'];

        $req = $this->request([
            "path" => "protocolseditquery/".$space['id'],
            "formid" => "protocolseditqueryform",
            "kit" => $kit,
            "no_proto" => 'noproto1',
            "proto" => $proto,
            "fixative" => $fixative,
            "option" => $option,
            "enzyme" => $enzyme,
            "dem" => $dem,
            "acl_inc" => $aciinc,
            "linker" => $linker,
            "inc" => $inc,
            "acll" => $acii,
            "inc2" => $inc,
            "associate" => 1,
            "id" => ""
        ]);
        $c = new ProtocolsController($req, $space);
        $data = $c->runAction('protocols', 'editquery', ['id_space' =>$space['id']]);
        $this->assertTrue($data['protocol']['id'] > 0);
        $protocol = $data['protocol']['id'];


        $req = $this->request([
            "path" => "anticorpsedit/".$space['id']."/0",
            "formid" => "antibodyEditForm",
            "name" => "antib1",
            "no_h2p2" => 1,
            "fournisseur" => 'f1',
            "id_source" => $source,
            "reactivity" => 'react1',
            'reference' => 'ref1',
            "clone" => 'clone1',
            "lot" => 'lot1',
            "id_isotype" => $isotype,
            "stockage" => 'stock1',
            "id_application" => $application,
            "id_staining" => $staining,
            "id" => 0
        ]);
        $c = new AntibodieslistController($req, $space);
        $data = $c->runAction('anticorps', 'edit', ['id_space' =>$space['id'], 'id' => 0]);
        $antibody = $this->assertTrue($data['antibody']['id'] > 0);
    }

}


?>
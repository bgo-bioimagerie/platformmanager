<?php

require_once 'Framework/Controller.php';

require_once 'Modules/antibodies/Model/Anticorps.php';
require_once 'Modules/antibodies/Model/Tissus.php';


class ImportantibodiesController extends Controller {

    public function indexAction() {

        // ---------- SETTINGS ----------
        $dsn_old = 'mysql:host=localhost;dbname=sygrrif2_h2p2;charset=utf8';
        $login_old = "root";
        $pwd_old = "root";

        $pdo_old = new PDO($dsn_old, $login_old, $pwd_old, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

        $this->importImages($pdo_old);
        echo "end <br/>";
    }

    public function importImages($pdo_old) {
        $sql = 'SELECT * FROM ac_anticorps WHERE image_url != ""';
        $result = $pdo_old->query($sql);
        $antibodies = $result->fetchAll();

        $model = new Anticorps();
        $modelTissus = new Tissus();
        foreach ($antibodies as $ac) {
            
            $tissus = $modelTissus->getTissus($ac['id']);
            
            if(count($tissus) > 0){
                echo 'import image ' . $ac['image_url'] . '<br/>';
                $modelTissus->setImageUrl($tissus[0]['id'], $ac['image_url']);
            }
        }
    }

}

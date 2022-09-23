<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreSpace.php';

# Upgrade: create dir /data/catalog/logos
class CoreUpgradeDB1661341705 extends Model {
    public function run(){
        Configuration::getLogger()->info("[db][upgrade] create dir data/catalog/logos");
        if (!file_exists('data/catalog/logos')) {
            mkdir('data/catalog/logos', 0755, true);
        }
    }
}
$db = new CoreUpgradeDB1661341705();
$db->run();
?>
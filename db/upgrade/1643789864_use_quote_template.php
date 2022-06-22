<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreSpace.php';

# Upgrade: use quote template
class CoreUpgradeDB1643789864 extends Model {
    public function run(){
        Configuration::getLogger()->info("[db][upgrade] Apply use quote template");
        $s = new CoreSpace();
        $spaces = $s->getSpaces('id');
        foreach($spaces as $space) {
            $id_space = $space['id'];
            if(!file_exists("data/quote/$id_space")) {
                mkdir("data/quote/$id_space", 0755, true);
            }
            if(file_exists("data/invoices/$id_space/template.twig") && !file_exists("data/quote/$id_space/template.twig")) {
                copy("data/invoices/$id_space/template.twig", "data/quote/$id_space/template.twig");
            } else if(file_exists("data/invoices/$id_space/template.php") && !file_exists("data/quote/$id_space/template.php")) {
                copy("data/invoices/$id_space/template.php", "data/quote/$id_space/template.php");
            }
        }
    }
}
$db = new CoreUpgradeDB1643789864();
$db->run();
?>

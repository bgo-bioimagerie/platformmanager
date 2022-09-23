<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: add missing base columns to se_project_user_table for upgrades
class CoreUpgradeDB1656925923 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply add missing base columns to se_project_user_table for upgrades");
    $cdb = new CoreDB();
    $cdb->base();
    Configuration::getLogger()->info("[db][upgrade] Apply add missing base columns to se_project_user_table for upgrades,done!");
  }
}
$db = new CoreUpgradeDB1656925923();
$db->run();
?>

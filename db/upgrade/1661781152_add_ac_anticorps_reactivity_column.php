<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: add reactivity column to ac_anticorps
class CoreUpgradeDB1661781152 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply add reactivity column to table ac_anticorps");
    $sql = 'ALTER TABLE ac_anticorps ADD COLUMN reactivity varchar(30) NOT NULL DEFAULT ""';
    $this->runRequest($sql);
    Configuration::getLogger()->info("[db][upgrade] Apply add reactivity column to table ac_anticorps,done!");
  }
}
$db = new CoreUpgradeDB1661781152();
$db->run();
?>
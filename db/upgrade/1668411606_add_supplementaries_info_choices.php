<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: add supplementaries info choices
class CoreUpgradeDB1668411606 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply add supplementaries info choices");
    $sql = 'ALTER TABLE bk_calsupinfo ADD COLUMN choices varchar(255) NOT NULL DEFAULT ""';
    $this->runRequest($sql);
    Configuration::getLogger()->info("[db][upgrade] Apply add supplementaries info choices, done!");
  }
}
$db = new CoreUpgradeDB1668411606();
$db->run();
?>

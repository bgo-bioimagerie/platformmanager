<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: increase_openid_oid_column 
class CoreUpgradeDB1642680081 extends Model {
  public function run(){
	  Configuration::getLogger()->info("[db][upgrade] Apply increase_openid_oid_column");
    $sql = "ALTER TABLE core_openid MODIFY COLUMN oid varchar(255) NOT NULL DEFAULT ''";
	  $this->runRequest($sql);
    Configuration::getLogger()->info("[db][upgrade] Apply increase_openid_oid_column, done! ");
  }
}
$db = new CoreUpgradeDB1642680081();
$db->run();
?>

<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: se_order allow date null
class CoreUpgradeDB1641978438 extends Model {
  public function run(){
	  Configuration::getLogger()->info("[db][upgrade] Apply se_order allow date null");
    Configuration::getLogger()->info("[db][upgrade] Run repair script 499 (PR #499)");
    $sql = "alter table se_order modify column date_open date NULL";
    $this->runRequest($sql);
    $sql = "alter table se_order modify column date_close date NULL";
    $this->runRequest($sql);
    $sql = "update se_order set date_close=null where date_close='0000-00-00'";
    $this->runRequest($sql);
    $sql = "update se_order set date_open=null where date_open='0000-00-00'";
    $this->runRequest($sql);
    Configuration::getLogger()->info("Run repair script 499 (PR #499), done!");
  }
}
$db = new CoreUpgradeDB1641978438();
$db->run();
?>

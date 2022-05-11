<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: autoincr acowner
class CoreUpgradeDB1651744382 extends Model {
  public function run(){
	  Configuration::getLogger()->info("[db][upgrade] Apply autoincr acowner");
	  $sql = 'ALTER TABLE ac_j_user_anticorps MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT';
	  $this->runRequest($sql);
	  Configuration::getLogger()->info("[db][upgrade] Apply autoincr acowner done");
  }
}
$db = new CoreUpgradeDB1651744382();
$db->run();
?>

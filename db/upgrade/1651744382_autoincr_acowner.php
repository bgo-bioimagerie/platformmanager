<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: autoincr acowner
class CoreUpgradeDB1651744382 extends Model {
  public function run(){
	  Configuration::getLogger()->info("[db][upgrade] Apply autoincr acowner");
	  try {
		$sql = 'ALTER TABLE ac_j_user_anticorps ADD COLUMN id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT';
		$this->runRequest($sql);
	  } catch(Throwable) {
		Configuration::getLogger()->warn("[db][upgrade] id column creation for ac_j_user_anticorps failed, this is fine as it may already exists");
	  }
	  $sql = 'ALTER TABLE ac_j_user_anticorps MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT';
	  $this->runRequest($sql);
	  Configuration::getLogger()->info("[db][upgrade] Apply autoincr acowner done");
  }
}
$db = new CoreUpgradeDB1651744382();
$db->run();
?>

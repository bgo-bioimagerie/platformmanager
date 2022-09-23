<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: autoincr acowner
class CoreUpgradeDB1651744382 extends Model {
  public function run(){
	  Configuration::getLogger()->info("[db][upgrade] Apply autoincr acowner");
	  if($this->checkColumn('ac_j_user_anticorps', 'id')) {
		Configuration::getLogger()->info('[db][upgrade] ac_j_user_anticorps.id already exists, modifying it');
		$sql = 'ALTER TABLE ac_j_user_anticorps MODIFY COLUMN id int(11) NOT NULL AUTO_INCREMENT';
		$this->runRequest($sql);
	  } else {
		Configuration::getLogger()->info('[db][upgrade] ac_j_user_anticorps.id does not exists, adding it');
		$sql = 'ALTER TABLE ac_j_user_anticorps ADD COLUMN id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT';
		$this->runRequest($sql);
	  }
	  Configuration::getLogger()->info("[db][upgrade] Apply autoincr acowner done");
  }
}
$db = new CoreUpgradeDB1651744382();
$db->run();
?>

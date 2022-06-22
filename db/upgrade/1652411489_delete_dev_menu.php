<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: delete dev menu
class CoreUpgradeDB1652411489 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply delete dev menu");
		$sql = 'DELETE FROM core_adminmenu WHERE name=?';
		$this->runRequest($sql, ['dev']);
    Configuration::getLogger()->info("[db][upgrade] Apply delete dev menu, done!");
  }
}
$db = new CoreUpgradeDB1652411489();
$db->run();
?>

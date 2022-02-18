<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: fix_corejspacesuser_status_column
class CoreUpgradeDB1642596593 extends Model {
  public function run(){
	  Configuration::getLogger()->info("[db][upgrade] Apply fix_corejspacesuser_status_column");
	  $sql = 'ALTER TABLE core_j_spaces_user MODIFY COLUMN status int NULL';
	  $this->runRequest($sql);
	  Configuration::getLogger()->info("[db][upgrade] Apply fix_corejspacesuser_status_column, done!");
  }
}
$db = new CoreUpgradeDB1642596593();
$db->run();
?>

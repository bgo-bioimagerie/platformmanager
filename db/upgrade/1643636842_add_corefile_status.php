<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: add corefile status
class CoreUpgradeDB1643636842 extends Model {
  public function run(){
	  Configuration::getLogger()->info("[db][upgrade] Apply add corefile status");
	  $this->addColumn('core_files', 'status', 'int', 0);
	  $this->addColumn('core_files', 'msg', 'varchar(255)', '');
  }
}
$db = new CoreUpgradeDB1643636842();
$db->run();
?>

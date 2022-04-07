<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/services/Model/SeTask.php';

# Upgrade: add se_kanban and se_task tables
class CoreUpgradeDB1647943110 extends Model {
	// TODO: [tracking] test db upgrade
  public function run(){
	  Configuration::getLogger()->info("[db][upgrade] Apply add se_task table");
	  $task = new SeTask();
	  $task->createTable();
	  Configuration::getLogger()->info("[db][upgrade] Apply add se_task table, done!");
  }
}
$db = new CoreUpgradeDB1647943110();
$db->run();
?>

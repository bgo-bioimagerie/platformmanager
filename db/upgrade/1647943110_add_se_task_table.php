<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/services/Model/SeTask.php';
require_once 'Modules/services/Model/SeTaskCategory.php';

# Upgrade: add se_task_category and se_task tables
class CoreUpgradeDB1647943110 extends Model {
  public function run() {
	  Configuration::getLogger()->info("[db][upgrade] Apply add se_task table");
	  $task = new SeTask();
	  $task->createTable();
	  Configuration::getLogger()->info("[db][upgrade] Apply add se_task table, done!");
	  Configuration::getLogger()->info("[db][upgrade] Apply add se_task_category table");
	  $task = new SeTaskCategory();
	  $task->createTable();
	  Configuration::getLogger()->info("[db][upgrade] Apply add se_task_category table, done!");
  }
}
$db = new CoreUpgradeDB1647943110();
$db->run();
?>

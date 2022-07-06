<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreCron.php';

# Upgrade: add cron
class CoreUpgradeDB1657120093 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply add cron");
    $task = new CoreCron();
	  $task->createTable();
    Configuration::getLogger()->info("[db][upgrade] Apply add cron, done!");

  }
}
$db = new CoreUpgradeDB1657120093();
$db->run();
?>

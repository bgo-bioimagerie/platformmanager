<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/services/Model/SeTask.php';
require_once 'Modules/services/Model/SeTaskCategory.php';

# Upgrade: add se_project_user table 
class CoreUpgradeDB1652360856 extends Model {
  public function run() {
	  Configuration::getLogger()->info("[db][upgrade] Apply add se_project_user table");
	  $project = new SeProject();
	  $project->createTable();
	  Configuration::getLogger()->info("[db][upgrade] Apply add se_project_user table, done!");
  }
}
$db = new CoreUpgradeDB1652360856();
$db->run();
?>
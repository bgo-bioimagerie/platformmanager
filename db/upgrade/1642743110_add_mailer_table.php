<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/mailer/Model/Mailer.php';

# Upgrade: add mailer table
class CoreUpgradeDB1642743110 extends Model {
  public function run(){
	  Configuration::getLogger()->info("[db][upgrade] Apply add mailer table");
	  $m = new Mailer();
	  $m->createTable();
  }
}
$db = new CoreUpgradeDB1642743110();
$db->run();
?>

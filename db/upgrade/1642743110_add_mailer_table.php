<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/mailer/Model/Mailer.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreConfig.php';

# Upgrade: add mailer table
class CoreUpgradeDB1642743110 extends Model {
  public function run(){
	  Configuration::getLogger()->info("[db][upgrade] Apply add mailer table");
	  $m = new Mailer();
	  $m->createTable();


	  $modelCoreConfig = new CoreConfig();
	  $cp = new CoreSpace();
	  $spaces = $cp->getSpaces('id');
	  foreach ($spaces as $space) {
		$id_space = $space['id'];
		$mailerEdit = $modelCoreConfig->getParamSpace('mailerEdit', $id_space, '');
		if(!$mailerEdit) {
		  $modelCoreConfig->setParam('mailerEdit', CoreSpace::$ADMIN, $id_space);
		}
	  }

  }
}
$db = new CoreUpgradeDB1642743110();
$db->run();
?>

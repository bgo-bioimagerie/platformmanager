<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: add on user deactivate option
class CoreUpgradeDB1647844521 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply add on user deactivate option");
    $this->addColumn('core_spaces', 'on_user_desactivate', "int", '0');
  }
}
$db = new CoreUpgradeDB1647844521();
$db->run();
?>

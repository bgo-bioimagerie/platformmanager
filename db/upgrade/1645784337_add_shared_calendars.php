<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: add shared calendars
class CoreUpgradeDB1645784337 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply add shared calendars");
    $this->addColumn('bk_schedulings', 'shared', 'tinyint', 0);
  }
}
$db = new CoreUpgradeDB1645784337();
$db->run();
?>

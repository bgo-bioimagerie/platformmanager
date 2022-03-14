<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: add bkcalentry reason
class CoreUpgradeDB1647241540 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply add bkcalentry reason");
    $this->addColumn('bk_calendar_entry', 'reason', 'int', 0);
    Configuration::getLogger()->info("[db][upgrade] Apply add bkcalentry reason, done!");
  }
}
$db = new CoreUpgradeDB1647241540();
$db->run();
?>

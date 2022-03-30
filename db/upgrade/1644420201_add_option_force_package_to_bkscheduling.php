<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: add option force_package to bkscheduling
class CoreUpgradeDB1644420201 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply add option force_package to bkscheduling");
    $this->addColumn('bk_schedulings', 'force_packages', "tinyint", 0);
  }
}
$db = new CoreUpgradeDB1644420201();
$db->run();
?>

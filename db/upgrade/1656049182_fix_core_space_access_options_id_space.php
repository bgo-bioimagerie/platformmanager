<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: fix core_space_access_options id_space
class CoreUpgradeDB1656049182 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply fix core_space_access_options id_space");
    $sql = "alter table core_space_access_options modify column id_space int not null default 0";
    $this->runRequest($sql);
    Configuration::getLogger()->info("[db][upgrade] Apply fix core_space_access_options id_space, done!");
  }
}
$db = new CoreUpgradeDB1656049182();
$db->run();
?>

<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: space termsofuse
class CoreUpgradeDB1645188538 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply space termsofuse");
    $this->addColumn('core_spaces', 'termsofuse', "varchar(255)", '');
  }
}
$db = new CoreUpgradeDB1645188538();
$db->run();
?>

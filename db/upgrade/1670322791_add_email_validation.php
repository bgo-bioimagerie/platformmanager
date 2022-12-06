<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: add email validation
class CoreUpgradeDB1670322791 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply add email validation");
    $this->addColumn('core_users', 'date_email_expiration', "int", 0);
    Configuration::getLogger()->info("[db][upgrade] Apply add email validation, done!");
  }
}
$db = new CoreUpgradeDB1670322791();
$db->run();
?>

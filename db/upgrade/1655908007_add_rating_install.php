<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/rating/Model/RatingInstall.php';

# Upgrade: add rating install
class CoreUpgradeDB1655908007 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply add rating install");
    $r = new RatingInstall();
    $r->createDatabase();
    Configuration::getLogger()->info("[db][upgrade] Apply add rating install, done!");
  }
}
$db = new CoreUpgradeDB1655908007();
$db->run();
?>

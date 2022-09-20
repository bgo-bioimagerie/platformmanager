<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreSpace.php';

# Upgrade: create missing views
class CoreUpgradeDB1657086545 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply create missing views");
    $model = new CoreSpace();
    $spaces = $model->getSpaces('id');
    foreach ($spaces as $space) {
      $model->createDbAndViews($space);
    }
    Configuration::getLogger()->info("[db][upgrade] Apply create missing views, done!");
  }
}
$db = new CoreUpgradeDB1657086545();
$db->run();
?>

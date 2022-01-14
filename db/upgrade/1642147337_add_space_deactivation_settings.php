<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: add space deactivation settings
class CoreUpgradeDB1642147337 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply add space deactivation settings");
    Configuration::getLogger()->debug('[db] New core space user deactivate option');
    $this->addColumn('core_spaces', 'user_desactivate', "int(1)", '1');
    Configuration::getLogger()->debug('[db] New core space user deactivate option, done!');
  }
}
$db = new CoreUpgradeDB1642147337();
$db->run();
?>

<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreUser.php';

# Upgrade: set_pwd_hash_algo
class CoreUpgradeDB1646377071 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply set_pwd_hash_algo");
    $this->addColumn('core_users', 'hash', 'int', CoreUser::$HASH_MD5);
  }
}
$db = new CoreUpgradeDB1646377071();
$db->run();
?>

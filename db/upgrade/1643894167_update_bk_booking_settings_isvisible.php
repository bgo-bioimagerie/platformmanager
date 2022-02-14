<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: update bk_booking_settings isvisible
class CoreUpgradeDB1643894167 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply update bk_booking_settings isvisible");
    $sql = "alter table bk_booking_settings modify column is_visible int NOT NULL";
        $this->runRequest($sql);
  }
}
$db = new CoreUpgradeDB1643894167();
$db->run();
?>

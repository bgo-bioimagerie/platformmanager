<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: add max duration to bkrestrictions
class CoreUpgradeDB1668442170 extends Model
{
  public function run()
  {
    Configuration::getLogger()->info("[db][upgrade] Apply add max duration to bkrestrictions");
    $sql = 'ALTER TABLE bk_restrictions ADD COLUMN maxduration varchar(50) NOT NULL DEFAULT ""';
    $this->runRequest($sql);
    $sql = 'ALTER TABLE bk_restrictions ADD COLUMN maxfulldays tinyint NOT NULL DEFAULT 0';
    $this->runRequest($sql);
    $sql = 'ALTER TABLE bk_restrictions ADD COLUMN disableoverclosed tinyint NOT NULL DEFAULT 0';
    $this->runRequest($sql);
    Configuration::getLogger()->info("[db][upgrade] Apply add max duration to bkrestrictions, done");
  }
}
$db = new CoreUpgradeDB1668442170();
$db->run();

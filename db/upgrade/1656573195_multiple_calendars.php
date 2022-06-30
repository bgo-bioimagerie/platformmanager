<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/booking/Model/BkScheduling.php';
require_once 'Modules/resources/Model/ReArea.php';

# Upgrade: multiple calendars
class CoreUpgradeDB1656573195 extends Model {
  public function run(){

    $bks = new BkResourceSchedule();
    $bks->createTable();
    Configuration::getLogger()->info("[db][upgrade] Apply multiple calendars");

    $sql = 'SELECT * from bk_schedulings WHERE deleted=0';
    $scheds = $this->runRequest($sql)->fetchAll();

    if (!empty($scheds) && isset($scheds[0]['name'])) {
      Configuration::getLogger()->info("[db][upgrade] Apply multiple calendars, nothing to to");
      return;
    }
    $sql = 'ALTER TABLE bk_schedulings ADD COLUMN name varchar(100)';
    $this->runRequest($sql);
    foreach ($scheds as $sched) {
      $ma = new ReArea();
      $areaName = $ma->getName($sched['id_space'], $sched['id_rearea']);
      $sql = 'UPDATE bk_schedulings SET name=? WHERE id=?';
      $this->runRequest($sql, [$areaName, $sched['id']]);
      $bks = new BkResourceSchedule();
      $bks->linkArea($sched['id_space'], $sched['id_rearea'], $sched['id']);
    }
    $sql = 'ALTER TABLE bk_schedulings DROP COLUMN id_rearea';
    $this->runRequest($sql);
    Configuration::getLogger()->info("[db][upgrade] Apply multiple calendars, done");

  }
}
$db = new CoreUpgradeDB1656573195();
$db->run();
?>

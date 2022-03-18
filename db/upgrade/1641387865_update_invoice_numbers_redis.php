<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreVirtual.php';
# Upgrade: Update invoice numbers in redis
class CoreUpgradeDB1641387865 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply Update invoice numbers in redis");
    $s = new CoreSpace();
    $spaces = $s->getSpaces('id');
      foreach($spaces as $space) {
      $sql = "SELECT * FROM in_invoice WHERE id_space=? ORDER BY number DESC;";
      $req = $this->runRequest($sql, [$space['id']]);
      $lastNumber = "";
      if ($req->rowCount() > 0) {
          $bill = $req->fetch();
          $lastNumber = $bill["number"];
          Configuration::getLogger()->debug('[invoice]', ['number' => $lastNumber]);
      }
      if ($lastNumber != "") {
          $lastNumber = explode("-", $lastNumber);
          $lastNumberY = $lastNumber[0];
          $lastNumberN = $lastNumber[1];
          if ($lastNumberY == date("Y", time())) {
              
              $cv = new CoreVirtual();

              Configuration::getLogger()->debug('[invoice][set]', ['space' => $space['id'], 'number' => intval($lastNumberN)]);
              $cv->set($space['id'], "invoices:$lastNumberY", intval($lastNumberN));
          }
      }
    }
    Configuration::getLogger()->info("[db][upgrade] Apply Update invoice numbers in redis, done!");



  }
}
$db = new CoreUpgradeDB1641387865();
$db->run();
?>

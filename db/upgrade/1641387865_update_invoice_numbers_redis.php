<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: Update invoice numbers in redis
class CoreUpgradeDB extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply Update invoice numbers in redis");
    $sql = "SELECT * FROM in_invoice ORDER BY number DESC;";
    $req = $this->runRequest($sql);
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
            $s = new CoreSpace();
            $spaces = $s->getSpaces('id');
            $cv = new CoreVirtual();
            foreach($spaces as $space) {
                Configuration::getLogger()->debug('[invoice][set]', ['space' => $space['id'], 'number' => $lastNumberN]);
                $cv->set($space['id'], "invoices:$lastNumberY", $lastNumberN);
            }
        }
    }
    Configuration::getLogger()->info("[db][upgrade] Apply Update invoice numbers in redis, done!");



  }
}
$db = new CoreUpgradeDB();
$db->run();
?>

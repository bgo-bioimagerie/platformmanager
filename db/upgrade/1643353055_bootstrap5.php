<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: bootstrap5
class CoreUpgradeDB1643353055 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply bootstrap5");
    $icons = [
      "glyphicon-registration-mark" => "bi-truck",
      "glyphicon-credit-card" => "bi-credit-card",
      "glyphicon-calendar" => "bi-calendar3",
      "glyphicon-plus" => "bi-basket",
      "glyphicon-euro" => "bi-currency-euro",
      "glyphicon-book" => "bi-book",
      "glyphicon-folder-open" => "bi-folder2-open",
      "glyphicon-signal" => "bi-bar-chart",
      "glyphicon-info-sign" => "bi-info-circle",
      "glyphicon-user" => "bi-person",
      "glyphicon-th-list" => "bi-list",
      "glyphicon-envelope" => "bi-envelope"

    ];
    foreach ($icons as $key => $value) {
      $sql = "UPDATE core_space_menus SET icon=? WHERE icon=?";
      $this->runRequest($sql, [$value, $key]);
      $sql = "UPDATE core_space_menus SET icon=? WHERE icon=?";
      $this->runRequest($sql, [$value, 'glyphicon '.$key]);
    }
  }
}
$db = new CoreUpgradeDB1643353055();
$db->run();
?>

<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreSpace.php';

# Upgrade: documents_visiblity
class CoreUpgradeDB1642773819 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply documents_visibility");
    $sql = "ALTER TABLE dc_documents ADD COLUMN visibility INT NOT NULL DEFAULT 0";
    $this->runRequest($sql);
    $sql = "ALTER TABLE dc_documents ADD COLUMN id_ref INT NOT NULL DEFAULT 0";
    $this->runRequest($sql);

    $modelCoreConfig = new CoreConfig();
    $cp = new CoreSpace();
    $spaces = $cp->getSpaces('id');
    foreach ($spaces as $space) {
      $id_space = $space['id'];
      $documentsEdit = $modelCoreConfig->getParamSpace('documentsEdit', $id_space, '');
      if(!$documentsEdit) {
        $modelCoreConfig->setParam('documentsEdit', CoreSpace::$MANAGER, $id_space);
      }
    }
    Configuration::getLogger()->info("[db][upgrade] Apply documents_visibility, done!");
  }
}
$db = new CoreUpgradeDB1642773819();
$db->run();
?>

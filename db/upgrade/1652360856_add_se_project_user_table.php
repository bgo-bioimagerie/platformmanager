<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Modules/services/Model/SeProject.php';

# Upgrade: add se_project_user table
class CoreUpgradeDB1652360856 extends Model {
	public function run() {
		Configuration::getLogger()->info("[db][upgrade] Apply add se_project_user table");
		$this->createTableInNotExists('se_project_user');
		$this->addColumn('se_project_user', 'id', 'int', 0);
		$this->addColumn('se_project_user', 'id_project', 'int', 0);
		$this->addColumn('se_project_user', 'id_user', 'int', 0);
		$this->addColumn('se_project_user', 'id_space', 'int', 0);
		Configuration::getLogger()->info("[db][upgrade] Apply add se_project_user table, done!");
	}
}
$db = new CoreUpgradeDB1652360856();
$db->run();
?>
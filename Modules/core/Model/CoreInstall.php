<?php

require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/FCache.php';
require_once 'Framework/Errors.php';
require_once 'Framework/Statistics.php';
require_once 'Framework/Events.php';


require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreFiles.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreAdminMenu.php';
require_once 'Modules/core/Model/CoreUserSettings.php';
require_once 'Modules/core/Model/CoreUserSpaceSettings.php';

require_once 'Modules/core/Model/CoreProjects.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreInstalledModules.php';
require_once 'Modules/core/Model/CoreMainMenu.php';
require_once 'Modules/core/Model/CoreMainSubMenu.php';
require_once 'Modules/core/Model/CoreMainMenuItem.php';
require_once 'Modules/core/Model/CoreMainMenuPatch.php';
require_once 'Modules/core/Model/CorePendingAccount.php';
require_once 'Modules/core/Model/CoreSpaceUser.php';
require_once 'Modules/core/Model/CoreSpaceAccessOptions.php';
require_once 'Modules/core/Model/CoreOpenId.php';
require_once 'Modules/core/Model/CoreAdminMenu.php';
require_once 'Modules/core/Model/CoreVirtual.php';
require_once 'Modules/users/Model/UsersPatch.php';


define("DB_VERSION", 2);
/**
 * Class defining the database version installed
 */
class CoreDB extends Model {


    public function upgrade_v0_v1() {

        Configuration::getLogger()->debug("[db] Old existing db patch");
        $modelMainMenuPatch = new CoreMainMenuPatch();
        $modelMainMenuPatch->patch();

        $model2 = new UsersPatch();
        $model2->patch();

        Configuration::getLogger()->debug("[db] Old existing db did not set id as primary/auto-increment");
        $modelSpaceUser = new CoreSpaceUser();
        $sql = "alter table core_j_spaces_user drop column id";
        $modelSpaceUser->runRequest($sql);
        $sql = "alter table core_j_spaces_user add column id int not null auto_increment primary key";
        $modelSpaceUser->runRequest($sql);
    }

    public function upgrade_v1_v2() {
        Configuration::getLogger()->debug("[db] Add core_spaces shortname, contact, support");
        $cp = new CoreSpace();
        $cp->addColumn('core_spaces', 'shortname', "varchar(30)", '');
        $cp->addColumn('core_spaces', 'contact', "varchar(100)", '');
        $cp->addColumn('core_spaces', 'support', "varchar(100)", '');
        $spaces = $cp->getSpaces('id');
        foreach ($spaces as $space) {
            if(!$space['shortname']) {
                $shortname = $space['name'];
                $shortname = strtolower($shortname);
                $shortname = str_replace(" ", "", $shortname);
                $cp->setShortname($space['id'], $shortname);
            }
        }
        Configuration::getLogger()->debug("[db] Add core_spaces shortname, contact, support, done!");

        Configuration::getLogger()->debug("[users] add apikey");
        $cu = new CoreUser();
        $cu->addColumn("core_users", "apikey", "varchar(30)", "");
        $allUsers = $cu->getAll();
        foreach ($allUsers as $user) {
            if($user['login'] == Configuration::get('admin_user') && $user['apikey'] != "") {
                continue;
            }    
            $cu->newApiKey($user['id']);
        }

        Configuration::getLogger()->debug("[adminmenu] remove update");
        $cam = new CoreAdminMenu();
        $cam->removeAdminMenu("Update");

        Configuration::getLogger()->debug('[id_space] set space identifier on objects');

        $sql = "SELECT * FROM `re_info`;";
        $resdb = $this->runRequest($sql)->fetchAll();
        foreach ($resdb as $res) {
            $sql = "UPDATE bk_access SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_authorization SET id_space=? WHERE resource_id=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_calendar_entry SET id_space=? WHERE resource_id=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_calquantities SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_owner_prices SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_packages SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_prices SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_restrictions SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_calsupinfo SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE re_event SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE re_resps SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }

        $sql = "SELECT * FROM `bk_packages`;";
        $resdb = $this->runRequest($sql)->fetchAll();
        foreach($resdb as $res) {
            $sql = "UPDATE bk_j_packages_prices SET id_space=? WHERE id_package=?";
            $this->runRequest($sql, array($res['id_space'], $res['id_package']));
        }

        $sql = "SELECT * FROM `bk_calendar_entry`;";
        $resdb = $this->runRequest($sql);
        while($res = $resdb->fetch()) {
            if($res['period_id']) {
                $sql = "UPDATE bk_calendar_period SET id_space=? WHERE id=?";
                $this->runRequest($sql, array($res['id_space'], $res['period_id']));
            }
        }

        $sql = "SELECT * FROM `re_area`;";
        $resdb = $this->runRequest($sql)->fetchAll();
        foreach ($resdb as $res) {
            $sql = "UPDATE bk_bookingcss SET id_space=? WHERE id_area=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_schedulings SET id_space=?, id_rearea=? WHERE id=?";
            $this->runRequest($sql, array($res['id_space'], $res['id'], $res['id']));
        }

        $sql = "SELECT * FROM `bj_collections`;";
        $resdb = $this->runRequest($sql)->fetchAll();
        foreach ($resdb as $res) {
            $sql = "UPDATE bj_j_collections_notes SET id_space=? WHERE id_collection=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }

        $sql = "SELECT * FROM `bj_notes`;";
        $resdb = $this->runRequest($sql);
        while($res = $resdb->fetch()) {
            $sql = "UPDATE bj_events SET id_space=? WHERE id_note=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bj_tasks SET id_space=? WHERE id_note=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bj_tasks_history SET id_space=? WHERE id_note=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }

        $sql = "SELECT * FROM `ca_categories`;";
        $resdb = $this->runRequest($sql)->fetchAll();
        foreach ($resdb as $res) {
            $sql = "UPDATE ca_entries SET id_space=? WHERE id_category=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }

        $sql = "SELECT * FROM `cl_clients`;";
        $resdb = $this->runRequest($sql)->fetchAll();
        foreach ($resdb as $res) {
            $sql = "UPDATE cl_addresses SET id_space=? WHERE id=?";
            $this->runRequest($sql, array($res['id_space'], $res['address_invoice']));
            $this->runRequest($sql, array($res['id_space'], $res['address_delivery']));
            $sql = "UPDATE cl_j_client_user SET id_space=? WHERE id_client=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }

        $sql = "SELECT * FROM `in_invoice`;";
        // $resdb = $this->runRequest($sql)->fetchAll();
        $resdb = $this->runRequest($sql);
        while($res = $resdb->fetch()) {
            $sql = "UPDATE in_invoice_item SET id_space=? WHERE id_invoice=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }

        $sql = "SELECT * FROM `qo_quotes`;";
        // $resdb = $this->runRequest($sql)->fetchAll();
        $resdb = $this->runRequest($sql);
        while($res = $resdb->fetch()) {
            $sql = "UPDATE qo_quoteitems SET id_space=? WHERE id_quote=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }

        $sql = "SELECT * FROM `re_event`;";
        // $resdb = $this->runRequest($sql)->fetchAll();
        $resdb = $this->runRequest($sql);
        while($res = $resdb->fetch()) {
            $sql = "UPDATE re_event_data SET id_space=? WHERE id_event=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }

        $sql = "SELECT * FROM `re_category`;";
        $resdb = $this->runRequest($sql)->fetchAll();
        foreach ($resdb as $res) {
            $sql = "UPDATE re_visas SET id_space=? WHERE id_resource_category=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }

        $sql = "SELECT * FROM `se_services`;";
        $resdb = $this->runRequest($sql)->fetchAll();
        foreach ($resdb as $res) {
            $sql = "UPDATE se_prices SET id_space=? WHERE id_service=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE se_purchase_item SET id_space=? WHERE id_service=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE se_service_types SET id_space=? WHERE id=?";
            $this->runRequest($sql, array($res['id_space'], $res['type_id']));
            $sql = "UPDATE se_order_service SET id_space=? WHERE id_service=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }

        $sql = "SELECT * FROM `stock_cabinets`;";
        $resdb = $this->runRequest($sql)->fetchAll();
        foreach ($resdb as $res) {
            $sql = "UPDATE stock_shelf SET id_space=? WHERE id_cabinet=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }

        $sql = "SELECT * FROM `es_sales`;";
        $resdb = $this->runRequest($sql);
        while($res = $resdb->fetch()) {
            $sql = "UPDATE es_sale_entered_items SET id_space=? WHERE id_sale=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE es_sale_history SET id_space=? WHERE id_sale=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE es_sale_items SET id_space=? WHERE id_sale=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE es_sale_invoice_items SET id_space=? WHERE id_sale=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }


        // Check
        $sql = "show tables";
        $tables = $this->runRequest($sql)->fetchAll();
        foreach($tables as $t) {
            $table = $t[0];
            $sql = "SELECT COUNT(*) as total FROM ".$table." WHERE id_space=0";
            $null_spaces = $this->runRequest($sql)->fetch();
            if($null_spaces && intval($null_spaces) > 0) {
                Configuration::getLogger()->warning('[id_space] found null space references', ['table' => $table, 'total' => $null_spaces]);
            }
        }

        Configuration::getLogger()->debug('[id_space] set space identifier on objects, done!');

        Configuration::getLogger()->debug('[virtual counter] init virtual counter');
        $counter = 0;
        $sql = "SELECT max(id_package) as counter FROM bk_owner_prices";
        $resdb = $this->runRequest($sql)->fetch();
        if($resdb && $resdb['counter']) {
            $counter = intval($resdb['counter']);
        }
        $sql = "SELECT max(id_package) as counter FROM bk_packages";
        $resdb = $this->runRequest($sql)->fetch();
        if($resdb && intval($resdb['counter']) > $counter) {
            $counter = intval($resdb['counter']);
        }
        $sql = "SELECT max(id_package) as counter FROM bk_prices";
        $resdb = $this->runRequest($sql)->fetch();
        if($resdb && intval($resdb['counter']) > $counter) {
            $counter = intval($resdb['counter']);
        }
        $sql = "SELECT max(id_supinfo) as counter FROM bk_calsupinfo";
        $resdb = $this->runRequest($sql)->fetch();
        if($resdb && intval($resdb['counter']) > $counter) {
            $counter = intval($resdb['counter']);
        }
        $sql = "SELECT max(id_quantity) as counter FROM bk_calquantities";
        $resdb = $this->runRequest($sql)->fetch();
        if($resdb && intval($resdb['counter']) > $counter) {
            $counter = intval($resdb['counter']);
        }
        $i = 0;
        while($i <= $counter) {
            $cvm = new CoreVirtual();
            $cvm->new('import');
            $i++;
        }

        Configuration::getLogger()->debug('[virtual counter] init virtual counter, done!');


        if(Statistics::enabled()) {
            Configuration::getLogger()->debug("[stats] import stats");
            $cp = new CoreSpace();
            $statHandler = new EventHandler();
            $spaces = $cp->getSpaces('id');
            foreach ($spaces as $space) {
                $statHandler->spaceCreate(['space' => ['id' => $space['id']]]);
                $spaceUsers = $cp->getUsers($space['id']);
                foreach ($spaceUsers as $spaceUser) {
                    $statHandler->spaceUserJoin([
                        'space' => ['id' => $space['id']],
                        'user' => ['id' => $spaceUser['id']]
                    ]);
                }
            }
            $statHandler->calentryImport();
            Configuration::getLogger()->debug('[stats] import stats, done!');
        }


    }

    /**
     * Get current database version
     */
    public function getRelease() {
        $sqlRelease = "SELECT * FROM `pfm_db`;";
        $reqRelease = $this->runRequest($sqlRelease);
        $release = null;
        if ($reqRelease && $reqRelease->rowCount() > 0){
            $release = $reqRelease->fetch();
            return $release['version'];
        }
        return 0;
    }

    /**
     * Get expected database version
     */
    public function getVersion() {
        return DB_VERSION;
    }

    /**
     * Create the table
     *
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `pfm_db` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
		    `version` varchar(255) NOT NULL DEFAULT ".DB_VERSION.",
		    PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);
    }

    public function upgrade() {
        $sqlRelease = "SELECT * FROM `pfm_db`;";
        $reqRelease = $this->runRequest($sqlRelease);

        Configuration::getLogger()->info("[db] set base columns if not present");
        $sql = "show tables";
        $tables = $this->runRequest($sql)->fetchAll();
        foreach($tables as $t) {
            $table = $t[0];
            $this->addColumn($table, "deleted", "int(1)", 0);
            $this->addColumn($table, "deleted_at", "DATETIME", "", true);
            $this->addColumn($table, "created_at", "TIMESTAMP", "INSERT_TIMESTAMP");
            $this->addColumn($table, "updated_at", "TIMESTAMP", "UPDATE_TIMESTAMP");
            $this->addColumn($table, "id_space", "int(11)", 0);
        }
        Configuration::getLogger()->info("[db] set base columns if not present, done!");

        $isNewRelease = false;
        $oldRelease = 0;
        $release = null;
        if ($reqRelease->rowCount() > 0){
            $release = $reqRelease->fetch();
            if($release['version'] != DB_VERSION) {
                $isNewRelease = true;
                $oldRelease = $release['version'];
            }
        } else {
            Configuration::getLogger()->info("[db] database release not set, setting it...", ["release" => 0]);
            $sql = "INSERT INTO pfm_db (version) VALUES(?)";
            $this->runRequest($sql, array(DB_VERSION));
            $isNewRelease = true;
            $reqRelease = $this->runRequest($sqlRelease);
            $release = $reqRelease->fetch();
        }

        if($isNewRelease) {
            Configuration::getLogger()->info("[db] Need to migrate", ["oldrelease" => $oldRelease, "release" => DB_VERSION]);
            $updateFromRelease = $oldRelease;
            $updateToRelease = $updateFromRelease + 1;
            $updateOK = true;
            while ($updateFromRelease < DB_VERSION) {
                Configuration::getLogger()->info("[db] Migrating", ["from" => $updateFromRelease, "to" => $updateToRelease]);
                $upgradeMethod = "upgrade_v".$updateFromRelease."_v".$updateToRelease;
                if (method_exists($this, $upgradeMethod)) {
                    try {
                        $this->$upgradeMethod();
                    } catch(Exception $e) {
                        $updateOK = false;
                        Configuration::getLogger()->error("[db] Migration failed", ["from" => $updateFromRelease, "to" => $updateToRelease]);
                        break;
                    }
                } else {
                    Configuration::getLogger()->info("[db] No migration available", ["from" => $updateFromRelease, "to" => $updateToRelease]);
                }

                Configuration::getLogger()->info("[db] updating database version...", ["release" => $updateToRelease]);
                $sql = "update pfm_db set version=? where id=?";
                $this->runRequest($sql, array($updateToRelease, $release['id']));

                $updateFromRelease = $updateToRelease;
                $updateToRelease = $updateFromRelease + 1;
            }
            if ($updateOK) {
                Configuration::getLogger()->info("[db] database migration over");
            } else {
                Configuration::getLogger()->error("[db] database migration failed!");
            }
        } else {
            Configuration::getLogger()->info("[db] no migration needed");
        }

    }
}


/**
 * Class defining the Install model
 * to edit the config file and initialize de database
 *
 * @author Sylvain Prigent
 */
class CoreInstall extends Model {


    public function createDatabase(){

        $modelCache = new FCache();
        $modelCache->freeTableURL();
        $modelCache->load();

        $modelConfig = new CoreConfig();
        $modelConfig->createTable();

        $modelConfig->initParam("admin_email", "firstname.name@company.com");
        $modelConfig->initParam("logo", "Modules/core/Theme/logo.jpg");        
    	$modelConfig->initParam("home_title", "Platform-Manager");
    	$modelConfig->initParam("home_message", "Connection");

        $modelConfig->setParam("navbar_bg_color", "#404040");
        $modelConfig->setParam("navbar_bg_highlight", "#333333");
        $modelConfig->setParam("navbar_text_color", "#e3e2e4");
        $modelConfig->setParam("navbar_text_highlight", "#ffffff");

        $modelUser = new CoreUser();
        $modelUser->createTable();
        $modelUser->installDefault();

        $modelUserS = new CoreUserSettings();
        $modelUserS->createTable();

        $moduleUserSpaceS = new CoreUserSpaceSettings();
        $moduleUserSpaceS->createTable();

        $modelMenu = new CoreAdminMenu();
        $modelMenu->createTable();
        $modelMenu->addCoreDefaultMenus();


        $modelStatus = new CoreStatus();
        $modelStatus->createTable();
        $modelStatus->createDefaultStatus();

        $modelProject = new CoreProjects();
        $modelProject->createTable();

        $modelSpace = new CoreSpace();
        $modelSpace->createTable();

        $modelModules = new CoreInstalledModules();
        $modelModules->createTable();

        $modelCoreMainMenu = new CoreMainMenu();
        $modelCoreMainMenu->createTable();

        $modelCoreMainSubMenu = new CoreMainSubMenu();
        $modelCoreMainSubMenu->createTable();

        $modelCoreMainMenuItem = new CoreMainMenuItem();
        $modelCoreMainMenuItem->createTable();

        $modelPending = new CorePendingAccount();
        $modelPending->createTable();

        $modelCoreSpaceUser = new CoreSpaceUser();
        $modelCoreSpaceUser->createTable();

        $modelCoreSpaceAccessOptions = new CoreSpaceAccessOptions();
        $modelCoreSpaceAccessOptions->createTable();

        $modelOpenid = new CoreOpenId();
        $modelOpenid-> createTable();

        $modelStatistics = new BucketStatistics();
        $modelStatistics->createTable();

        $modelCoreFiles = new CoreFiles();
        $modelCoreFiles->createTable();

        $modelVirtual = new CoreVirtual();
        $modelVirtual->createTable();

        if (!file_exists('data/conventions/')) {
            mkdir('data/conventions/', 0777, true);
        }

    }
    /**
     * Test if the database informations are correct
     *
     * @param string $sql_host Host of the database (ex: localhost)
     * @param string $login Login to connect to the database (ex: root)
     * @param string $password Password to connect to the database
     * @param string $db_name Name of the database
     * @return string error message
     */
    public function testConnection($sql_host, $login, $password, $db_name) {

        try {
            $dsn = 'mysql:host=' . $sql_host . ';dbname=' . $db_name . ';charset=utf8';
            $testdb = new PDO($dsn, $login, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $testdb->exec("SHOW TABLES");
            return 'success';
        } catch (PfmDbException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Save the database connection information into the config file
     *
     * @param string $sql_host Host of the database (ex: localhost)
     * @param string $login Login to connect to the database (ex: root)
     * @param string $password Password to connect to the database
     * @param string $db_name Name of the database
     * @return boolean false if unable to write in the file
     */
    public function writedbConfig($sql_host, $login, $password, $db_name) {

        $dsn = '\'mysql:host=' . $sql_host . ';dbname=' . $db_name . ';charset=utf8\'';

        $fileURL = Configuration::getConfigFile();
        return $this->editConfFile($fileURL, $dsn, $login, $password);
    }

    /**
     * Internal function that implement the config file edition
     *
     * @param string $fileURL URL of the configuration file
     * @param string $dsn Connection informations for the PDO connection
     * @param string $login Login to connect to the database (ex: root)
     * @param string $password Password to connect to the database
     * @return boolean
     */
    protected function editConfFile($fileURL, $dsn, $login, $password) {

        $handle = @fopen($fileURL, "r");
        $outContent = '';
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {

                // replace dsn
                $outbuffer1 = $this->replaceContent($buffer, 'dsn', $dsn);
                if ($outbuffer1 != ""){
                    $outContent = $outContent . $outbuffer1;
                    continue;
                }

                // replace login
                $outbuffer2 = $this->replaceContent($buffer, 'login', $login);
                if ($outbuffer2 != ""){
                    $outContent = $outContent . $outbuffer2;
                    continue;
                }

                // replace pwd
                $outbuffer3 = $this->replaceContent($buffer, 'pwd', $password);
                if ($outbuffer3 != ""){
                    $outContent = $outContent . $outbuffer3;
                    continue;
                }

                $outContent = $outContent . $buffer;
            }
            if (!feof($handle)) {
                echo "Erreur: fgets() failed \n";
            }
            fclose($handle);
        } else {
            return false;
        }

        // save the new cong file
        $fp = fopen($fileURL, 'w');
        fwrite($fp, $outContent);
        fclose($fp);
        return true;
    }

    private function replaceContent($buffer, $varName, $varContent) {
        $content = "";
        $pos = strpos($buffer, $varName);
        if ($pos === false) {

        } else if ($pos == 0) {
            $content = $varName . ' = ' . $varContent . PHP_EOL;
        }
        return $content;
    }
}

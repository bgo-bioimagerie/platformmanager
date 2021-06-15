<?php

require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/FCache.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreAdminMenu.php';
require_once 'Modules/core/Model/CoreUserSettings.php';
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
require_once 'Modules/users/Model/UsersPatch.php';


define("DB_VERSION", 2);
/**
 * Class defining the database version installed
 */
class CoreDB extends Model {


    public function upgrade_v0_v1() {
        $modelMainMenuPatch = new CoreMainMenuPatch();
        $modelMainMenuPatch->patch();

        $model2 = new UsersPatch();
        $model2->patch();
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
    }

    /**
     * Get current database version
     */
    public function getRelease() {
        $sqlRelease = "SELECT * FROM `pfm_db`;";
        $reqRelease = $this->runRequest($sqlRelease);
        $release = null;
        if ($reqRelease->rowCount() > 0){
            $release = $reqRelease->fetch();
            return $release['version'];
        }
        return 0;
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

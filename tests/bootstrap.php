<?php
require_once 'Framework/Configuration.php';
require_once 'Framework/FCache.php';
require_once 'Modules/core/Model/CoreInstall.php';
require_once 'Modules/core/Model/CoreUser.php';

session_start();
$logger = Configuration::getLogger();
$logger->info("Installing database from ". Configuration::getConfigFile());

// drop all content if exists
$cdb = new CoreDB();
$cdb->dropAll();

$doInstall = getenv("INSTALL");
if($doInstall !== false && $doInstall === "0") {
    $m = new CoreUser();
    $m->installDefault();
    $modelCache = new FCache();
    $modelCache->freeTableURL();
    $modelCache->load();
    return;
    // Just add admin user
}


// Create db release table if not exists
$cdb = new CoreDB();
$cdb->createTable();

$modelCreateDatabase = new CoreInstall();
$modelCreateDatabase->createDatabase();
$logger->info("Database installed");


$logger->info("Upgrading modules");

$modulesInstalled = '';

try {
    $first = true;
    $modules = Configuration::get('modules');
    for ($i = 0; $i < count($modules); ++$i) {
        $moduleName = ucfirst(strtolower($modules[$i]));
        if ($moduleName == 'Core') {
            continue;
        }
        $installFile = "Modules/" . $modules[$i] . "/Model/" . $moduleName . "Install.php";
        if (file_exists($installFile)) {
            $logger->info('Update database for module ' . $moduleName . " => ". $installFile);
            if (!$first){
                $modulesInstalled .= ", ";
            }
            else{
                $first = false;
            }
            $modulesInstalled .= $modules[$i];
            require_once $installFile;
            $className = $moduleName . "Install";
            $object = new $className();
            $object->createDatabase();
            $logger->info('update database for module ' .$modules[$i]  . "done");
        }
    }
} catch (Exception $e) {
        $logger->error("Error", ["error" => $e->getMessage()]);
}

// update db release and launch upgrade
$cdb->upgrade();

$logger->info("Upgrade done!", ["modules" => $modulesInstalled]);

?>

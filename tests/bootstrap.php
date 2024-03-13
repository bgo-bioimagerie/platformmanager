<?php
require_once 'Framework/Configuration.php';
require_once 'Framework/FCache.php';
require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreInstall.php';
require_once 'Modules/core/Model/CoreUser.php';

session_start();
$logger = Configuration::getLogger();

$skipInstall = boolval(getenv("SKIP_INSTALL"));
$skipDrop = boolval(getenv("SKIP_DROP"));
$skipConstraints = boolval(getenv("SKIP_CONSTRAINTS"));

$logger->info("Bootstrapping tests ! (skipInstall => $skipInstall, skipDrop => $skipDrop, skipConstraints => $skipConstraints)");

$pdo = Model::getDatabase();
$cdb = new CoreDB();

if (!$skipDrop)
    $cdb->dropAll(contentOnly: $skipInstall); // drop all content if exists

if ($skipInstall) {
    $m = new CoreUser();
    $m->installDefault();

    // set by db/pre_constraints.sql when SKIP_INSTALL = 0
    $pdo->exec("INSERT INTO core_user_space_roles  (id,label)
	            VALUES (0,'inactive')
	                 , (1,'visitor')
	                 , (2,'user')
	                 , (3,'manager')
	                 , (4,'admin')
                ON DUPLICATE KEY UPDATE label = label;");

    $modelCache = new FCache();
    $modelCache->freeTableURL();
    $modelCache->load();

    return; // Just add admin user
}

$logger->info("Installing database from " . Configuration::getConfigFile());

// Create db release table if not exists
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
            $logger->info('Update database for module ' . $moduleName . " => " . $installFile);
            if (!$first) {
                $modulesInstalled .= ", ";
            } else {
                $first = false;
            }
            $modulesInstalled .= $modules[$i];
            require_once $installFile;
            $className = $moduleName . "Install";
            $object = new $className();
            $object->createDatabase();
            $logger->info('update database for module ' . $modules[$i] . "done");
        }
    }
} catch (Exception $e) {
    $logger->error("Error", ["error" => $e->getMessage()]);
}

// update db release and launch upgrade
$cdb->upgrade();
$cdb->scanUpgrades();
$cdb->base();

$logger->info("Upgrade done!", ["modules" => $modulesInstalled]);

// run sql constraints scripts
if (!$skipConstraints) {

    $logger->info("Running sql pre_constraints script");

    $pre_constraints = file_get_contents("db/pre_constraints.sql");
    $pdo->exec($pre_constraints);

    $logger->info("Running sql constraints script");

    $constraints = file_get_contents("db/constraints.sql");
    $pdo->exec($constraints);

    $logger->info("Done running sql constraints scripts !");
}

?>

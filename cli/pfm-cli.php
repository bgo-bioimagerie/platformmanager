<?php
require __DIR__ . '/../vendor/autoload.php';

require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreInstall.php';
require_once 'Modules/core/Model/CoreUser.php';

use Garden\Cli\Cli;

function version()
    {
        $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));
        $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
        $commitDate->setTimezone(new \DateTimeZone('UTC'));
        return sprintf('%s (%s)', $commitHash, $commitDate->format('Y-m-d H:i:s'));
    }

$cli = Cli::create()
    ->command('install')
    ->description('Install/upgrade database and routes')
    ->command('expire')
    ->description('Expire in spaces old users (not logged for a year or contract ended)')
    ->command('version')
    ->description('Show version')
    ->opt('db:d', 'Show installed and expected db version', false, 'boolean');

$args = $cli->parse($argv);

switch ($args->getCommand()) {
    case 'install':
        cliInstall();
        break;
    case 'expire':
        $logger = Configuration::getLogger();
        $logger->info("Expire old users");
        $modelUser = new CoreUser();
        $count = $modelUser->disableUsers(6);
        $logger->info("Expired ".$count. " users");
        break;
    case 'version':
        echo "Version: ".version()."\n";
        if ($args->getOpt('db')) {
            $cdb = new CoreDB();
            $crel = $cdb->getRelease();
            echo "DB installed version: ".$crel."\n";
            echo "DB expected version: ".$cdb->getVersion()."\n";
        }
        break;
    default:
        break;
}

function cliInstall() {
    $logger = Configuration::getLogger();
    $logger->info("Installing database from ". Configuration::getConfigFile());

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

}

?>


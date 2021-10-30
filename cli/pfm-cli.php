<?php
require __DIR__ . '/../vendor/autoload.php';

require_once 'Framework/Configuration.php';
require_once 'Framework/FCache.php';
require_once 'Framework/Events.php';
require_once 'Framework/Statistics.php';
require_once 'Modules/core/Model/CoreInstall.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreConfig.php';


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
    ->opt('from', 'Force install from release', false, 'integer')
    ->command('routes')
    ->description('manage routes')
    ->opt('reload:r', 'Reload routes from code', false, 'boolean')
    ->command('expire')
    ->description('Expire in spaces old users (according to global config)')
    ->opt('del:d', 'Remove user from space, else just set as inactive', false, 'boolean')
    ->command('version')
    ->description('Show version')
    ->opt('db:d', 'Show installed and expected db version', false, 'boolean')
    ->command('stats')
    ->opt('import', '(re)import all stats', false, 'boolean')
    ->command('repair')
    ->opt('bug', 'Bug number', 0, 'integer');

$args = $cli->parse($argv);

try {
    switch ($args->getCommand()) {
        case 'repair':
            cliFix($args->getOpt('bug', 0));
            break;
        case 'install':
            cliInstall($args->getOpt('from', -1));
            break;
        case 'routes':
            if($args->getOpt('reload')) {
                $modelCache = new FCache();
                $modelCache->freeTableURL();
                $modelCache->load();
            }
            break;
        case 'expire':
            $logger = Configuration::getLogger();
            $logger->info("Expire old users");
            $modelUser = new CoreUser();
            $modelSettings = new CoreConfig();
            $desactivateSetting = $modelSettings->getParam("user_desactivate", 6);
            $count = $modelUser->disableUsers(intval($desactivateSetting), $args->getOpt('del'));
            $logger->info("Expired ".$count. " users");
            break;
        case 'stats':
            $logger = Configuration::getLogger();
            $logger->info("Import stats");
            statsImport();
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
} catch(Throwable $e) {
    Configuration::getLogger()->error('Something went wrong', ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
}

function statsImport() {
    $cp = new CoreSpace();
    Configuration::getLogger()->debug("[stats] create bucket");
    $spaces = $cp->getSpaces('id');
    foreach ($spaces as $space) {
        $statHandler = new Statistics();
        $statHandler->createDB($space['shortname']);
    }
    Configuration::getLogger()->debug("[stats] create bucket, done!");

    Configuration::getLogger()->debug("[stats] import calentry stats");
    $eventHandler = new EventHandler();
    $eventHandler->calentryImport();
    Configuration::getLogger()->debug('[stats] import calentry stats, done!');

    Configuration::getLogger()->debug("[stats] import invoice stats");
    $statHandler = new EventHandler();
    $statHandler->invoiceImport();
    Configuration::getLogger()->debug('[stats] import invoice stats, done!');
}

function cliFix($bug=0) {
    if($bug<=0) {
        Configuration::getLogger()->error('No bug specified', []);
        return;
    }
    $cdb = new CoreDB();
    try {
        call_user_func_array(array($cdb, 'repair'.$bug), []);
    } catch(Throwable $e) {
        Configuration::getLogger()->error('Repair error', ['error' => $e->getMessage()]);
    }
}

function cliInstall($from=-1) {
    $logger = Configuration::getLogger();
    $logger->info("Installing database from ". Configuration::getConfigFile());

    // Create db release table if not exists
    $cdb = new CoreDB();
    $freshInstall = $cdb->isFreshInstall();
    if($from == -1 && $freshInstall) {
        $from = 0;
    }
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
    $cdb->upgrade($from);

    $logger->info("Upgrade done!", ["modules" => $modulesInstalled]);

}

?>


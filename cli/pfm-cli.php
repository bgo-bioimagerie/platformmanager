<?php
require __DIR__ . '/../vendor/autoload.php';

require_once 'Framework/Configuration.php';
require_once 'Framework/FCache.php';
require_once 'Framework/Events.php';
require_once 'Framework/Statistics.php';
require_once 'Framework/Router.php';

require_once 'Modules/core/Model/CoreInstall.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreConfig.php';

use Symfony\Component\Yaml\Yaml;
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
    ->command('space')
    ->description('show space info')
    ->opt('flags', 'Show space plan flags', false, 'boolean')
    ->opt('id', 'space identifier', 0, 'integer')
    ->command('routes')
    ->description('manage routes')
    ->opt('reload:r', 'Reload routes from code', false, 'boolean')
    ->opt('yaml:y', 'Yaml output', false, 'boolean')
    ->command('expire')
    ->description('Expire in spaces old users (according to global config)')
    ->opt('del:d', 'Remove user from space, else just set as inactive', false, 'boolean')
    ->command('version')
    ->description('Show version')
    ->opt('db:d', 'Show installed and expected db version', false, 'boolean')
    ->command('stats')
    ->opt('import', '(re)import all stats', false, 'boolean')
    ->command('cache')
    ->opt('clear', 'Clear caches', false, 'boolean')
    ->opt('dry', 'Dry run', false, 'boolean')
    ->command('config')
    ->opt('yaml:y', 'Yaml output', false, 'boolean')
    ->command('repair')
    ->opt('bug', 'Bug number', 0, 'integer');

$args = $cli->parse($argv);

try {
    switch ($args->getCommand()) {
        case 'config':
            $params =  Configuration::getParameters();
            Configuration::getLogger()->error("?", ["d"=>$args->getOpt('yaml', false)]);
            if($args->getOpt('yaml', false)) {
                echo Yaml::dump(['config' => $params], 10);
            } else {
                ksort($params);
                foreach($params as $key => $value) {
                    if(is_array($value)) {
                        foreach($value as $v){
                            echo $key.'[] = '.$v."\n";
                        }
                    } else {
                    echo "$key = $value\n";
                    }
                }
            }
            break;
        case 'space':
            cliSpaceShow($args->getOpt('id', 0), $args->getOpt('flags', false));
            break;
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
            } else {
                $r = new Router();
                $rl = $r->listRoutes();
                echo "Routes:\n";
                $routes = [];
                foreach ($rl as $rll) {
                    $routes[] = ['methods' => $rll[0], 'url' => $rll[1], 'action' => $rll[2]];
                    //echo "* $rll[0] - $rll[1] - $rll[2]\n";
                }
                $fc = new FCache();
                $rl = $fc->listAll();
                foreach ($rl as $rll) {
                    $route = ['methods' => 'GET|POST', 'action' => $rll[1]];
                    // $url = "* GET|POST - $rll[0] /$rll[0]";
                    $u = '/'.$rll[0];
                    for($i=2;$i<count($rll);$i++) {
                        // $url .= "/[i:".$rll[$i]."]";
                        $u .= "/[i:".$rll[$i]."]";
                    }
                    $route['url'] = $u;
                    // $url .= " - ".$rll[1];
                    $routes[] = $route;
                }
                if($args->getOpt('yaml')) {
                    $out = ['routes' => $routes];
                    echo Yaml::dump($out, 3)."\n";
                } else {
                    foreach ($routes as $route) {
                        echo "* ".$route['methods'].' - '.$route['url']. ' - '.$route['action']."\n";
                    }
                }
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
        case 'cache':
            if($args->getOpt('clear')) {
                cacheClear($args->getOpt('dry'));
            }
            break;
        default:
            break;
    }
} catch(Throwable $e) {
    Configuration::getLogger()->error('Something went wrong', ['error' => $e->getMessage(), 'stack' => $e->getTraceAsString()]);
}

function cliSpaceShow(int $id, bool $flags) {
    $cp = new CoreSpace();
    Configuration::getLogger()->debug("[space] get space");
    $space = $cp->getSpace($id);

    $plan = new CorePlan($space['plan'], $space['plan_expire']);
    foreach ($space as $key => $value) {
        if (is_int($key)) {
            unset($space[$key]);
        }
    }
    echo Yaml::dump(['space' => $space], 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
    if($flags) {
        $f = $plan->Flags();
        echo Yaml::dump(['flags' => $f], 2, 4, Yaml::DUMP_OBJECT_AS_MAP);
    }
}

function statsImport() {
    $cp = new CoreSpace();
    Configuration::getLogger()->info("[stats] create bucket");
    $spaces = $cp->getSpaces('id');
    $eventHandler = new EventHandler();
    foreach ($spaces as $space) {
        $eventHandler->spaceCreate(['space' => ['id' => $space['id']]]);
    }
    Configuration::getLogger()->info("[stats] create bucket, done!");

    Configuration::getLogger()->info("[stats] import calentry stats");
    $eventHandler = new EventHandler();
    $eventHandler->calentryImport();
    Configuration::getLogger()->info('[stats] import calentry stats, done!');

    Configuration::getLogger()->info("[stats] import invoice stats");
    $statHandler = new EventHandler();
    $statHandler->invoiceImport();
    Configuration::getLogger()->info('[stats] import invoice stats, done!');

    Configuration::getLogger()->info("[stats] import customer stats");
    $eventHandler->customerImport();
    Configuration::getLogger()->info("[stats] import customer stats, done!");

    Configuration::getLogger()->info("[stats] import resource stats");
    $eventHandler->resourceImport();
    Configuration::getLogger()->info("[stats] import resource stats, done!");

    Configuration::getLogger()->info("[stats] import quote stats");
    $eventHandler->quoteImport();
    Configuration::getLogger()->info("[stats] import quote stats, done!");

    Configuration::getLogger()->info("[stats] import service stats");
    $eventHandler->serviceImport();
    Configuration::getLogger()->info("[stats] import service stats, done!");

    Configuration::getLogger()->info("[stats] import tickets stats");
    foreach ($spaces as $space) {
        $eventHandler->ticketCount(["space" => ["id" => $space["id"]]]);
    }
    Configuration::getLogger()->info("[stats] import tickets stats, done!");


}

function cacheClear($dry) {
    if(is_dir('/tmp/pfm')) {
       removeDirectory('/tmp/pfm', $dry);
       Configuration::getLogger()->info('cache cleared');
    } else {
        Configuration::getLogger()->info('nothing to do');
    }
}

function removeFile($path, $dry=false) {
    if($dry) {
        Configuration::getLogger()->info('[delete]', ['path' => $path]);
    } else {
	    unlink($path);
    }
}
function removeDirectory($path, $dry=false) {
	$files = glob($path . '/*');
	foreach ($files as $file) {
		is_dir($file) ? removeDirectory($file, $dry) : removeFile($file, $dry);
	}
    if($dry) {
        Configuration::getLogger()->info('[delete]', ['path' => $path]);
    } else {
	    rmdir($path);
    }
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


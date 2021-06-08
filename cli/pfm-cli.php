<?php
require __DIR__ . '/../vendor/autoload.php';

require_once 'Framework/Configuration.php';
require_once 'Modules/core/Model/CoreInstall.php';
require_once 'Modules/core/Model/CoreUser.php';

function version()
    {
        $commitHash = trim(exec('git log --pretty="%h" -n1 HEAD'));
        $commitDate = new \DateTime(trim(exec('git log -n1 --pretty=%ci HEAD')));
        $commitDate->setTimezone(new \DateTimeZone('UTC'));
        return sprintf('%s (%s)', $commitHash, $commitDate->format('Y-m-d H:i:s'));
    }

$logger = Configuration::getLogger();

$shortopts = "";
$shortopts .= "i";
$shortopts .= "e";
$shortopts .= "h";
$shortopts .= "v";
$longopts = array(
  "install",
  "expire",
  "help",
  "version"
);
$options = getopt($shortopts, $longopts);


if (empty($options) || isset($options['h']) || isset($options['help'])) {
    echo "Usage:\n";
    echo " --install: create and updates tables in database\n";
    echo " --expire: expire user not logged since 1 year or contract ended\n";
    echo " --version: show software version\n";
    return;
}

if (isset($options['v']) || isset($options['version'])) {
    $v = version();
    echo "Version: " . $v . "\n";
    return;
}

if (isset($options['expire']) || isset($options['e'])) {
    $logger->info("Expire old users");
    $modelUser = new CoreUser();
    $count = $modelUser->disableUsers(6);
    $logger->info("Expired ".$count. " users");
    return;
}

if (isset($options['install']) || isset($options['i'])) {
    $logger->info("Installing database from ". Configuration::getConfigFile());

    $modelCreateDatabase = new CoreInstall();
    $dsn = Configuration::get('dsn');
    $login = Configuration::get('login');
    $password = Configuration::get('pwd');
    $modelCreateDatabase->setDatabase($dsn, $login, $password);
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

    $cdb = new CoreDB();
    $cdb->createTable();

    $logger->info("Upgrade done!", ["modules" => $modulesInstalled]);

}

?>


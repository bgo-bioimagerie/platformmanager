<?php require_once 'Framework/ti.php' ?>
<?php
require_once 'Modules/core/Model/CoreInstall.php';
use DebugBar\StandardDebugBar;
use DebugBar\DataCollector\PDO\PDOCollector;

$isdev = (getenv('PFM_MODE') == 'dev');
if($isdev) {
    CoreInstall::getDatabase();
    $debugbar = new StandardDebugBar();
    $debugbarRenderer = $debugbar->getJavascriptRenderer();
    $debugbar->addCollector(new DebugBar\DataCollector\PDO\PDOCollector(CoreInstall::getDatabase()));
}
?>
<!DOCTYPE html>
<html lang="<?php if(isset($lang)) {echo $lang;} else {echo "en";} ?>">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <?php
            if (isset($metadesc)) {echo "<meta name=\"description\" content=\"$metadesc\"/>\n";}
        ?>
        <meta name="mode" description="{{$isdev}}">
        <base href="<?php echo $rootWeb ?>" >
        <title>
            <?php startblock('title') ?>
            Platform-Manager
            <?php endblock() ?>
        </title>
        <?php
        if($isdev) {
            echo '<script src="externals/vuejs/vue.js"></script>';
            echo $debugbarRenderer->renderHead();
        } else {
            echo '<script src="externals/vuejs/vue.min.js"></script>';
        }
        ?>
        <link rel="stylesheet" href="externals/bootstrap/css/bootstrap.min.css">
        
        <link href="externals/core/theme/navbar-fixed-top.css" rel="stylesheet">
        <link rel="stylesheet" href="Modules/core/Theme/core.css">
        <link rel="stylesheet" href="Modules/core/Theme/space.css">
        <link rel='stylesheet' href='Modules/core/Theme/spacemenu.css' />
        <link href="externals/datepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
        <script src="externals/jquery-1.11.1.js"></script>
        <script src="externals/bootstrap/js/bootstrap.min.js"></script>

        <?php startblock('stylesheet') ?>
        <?php endblock() ?>

    </head>
    <body style="background-color: #e7ecf0;">

        <?php startblock('navbar') ?>
            <?php
                require_once 'Modules/core/Controller/CorenavbarController.php';
                $navController = new CorenavbarController(new Request(array(), false));
                echo $navController->navbar();
            ?>
        <?php endblock() ?>
        <?php startblock('spacenavbar') ?>
        <?php endblock() ?>
        <div class="container row">
                <?php startblock('spacemenu') ?>
                <?php endblock() ?>


                <div id="app" class="container">
                    <?php if (isset($flash) && $flash) { ?>
                        <div class="alert alert-<?php echo $flash['class']; ?> alert-dismissible  show" role="alert">
                            <?php echo $flash['msg']; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php }?>
                    
                    <?php startblock('content') ?>
                    <?php endblock() ?>

            </div>
        </div>

        <?php startblock('footer') ?>
        <footer>
        <div class="container">
            <div class="row">
                <div class="col-sm-4"><a href="http://bgo-bioimagerie.github.io/platformmanager/">Documentation</a></div>
                <div class="col-sm-4"><a href="/coreabout">About</a></div>
            </div>
        </div>
        </footer>
        <?php endblock() ?>

        <?php
        if($isdev) {
            echo $debugbarRenderer->render();
        }
        ?>
    </body>
</html>

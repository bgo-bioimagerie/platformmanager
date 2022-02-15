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
        <meta name="mode" description="<?php echo $isdev ?>">
        <base href="<?php echo  $context['rootWeb'] ?>" >
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
        <link rel="stylesheet" href="externals/node_modules/bootstrap/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="externals/node_modules/bootstrap-icons/font/bootstrap-icons.css">

        <link href="externals/core/theme/navbar-fixed-top.css" rel="stylesheet">
        <script src="externals/jquery-1.11.1.js"></script>

        <?php startblock('stylesheet') ?>
        <?php endblock() ?>

        <link rel="stylesheet" href="Modules/core/Theme/core.css">
        <link rel="stylesheet" href="Modules/core/Theme/space.css">
        <link rel='stylesheet' href='/Modules/core/Theme/spacemenu.css' />

    </head>
    <body style="background-color: #e7ecf0;">

        <?php startblock('navbar') ?>
            <?php
            $nav = new Navbar($context['lang']);
            echo $nav->get();
            ?>
        <?php endblock() ?>

        <div id="mainmenu" style="margin-top: 5px; margin-bottom: 2px;">
        <?php
        if ($context['mainMenu']) {
            echo $context['mainMenu'];
        }
        ?>
        </div>

        <div class="container-fluid">
                <div class="row" id="app" >
                    <?php if(isset($context['maintenance']) && $context['maintenance']) { ?>
                        <div class="container">
                            <div class="alert alert-warning alert-dismissible  show" role="alert">
                                <?php echo $context['maintenance']; ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        </div>                        
                    <?php } ?>
                    <?php if (isset($flash) && $flash) { ?>
                        <div class="col-12">
                            <div class="alert alert-<?php echo $flash['class']; ?> alert-dismissible  show" role="alert">
                                <?php echo $flash['msg']; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                    <?php }?>
                    
                        <?php startblock('spacemenu') ?>
                        <?php
                        if ($context['spaceMenu']) {
                        ?>
                        <div class="col-12">
                        <?php
                            echo $context['spaceMenu'];
                        ?>
                        </div>
                        <?php } ?>
                        <?php endblock() ?>
                    <?php
                    if ($context['sideMenu']) {
                    ?>
                    <div class="col-12 col-md-2" id="sidemenu">
                    <?php
                        echo $context['sideMenu'];
                    ?>
                    </div>
                    <div class="col-12 col-md-10" id="content">
                    <?php } else { ?>
                    <div class="col-12" id="content">
                    <?php } ?>
                    <?php startblock('content') ?>
                    <?php endblock() ?>
                    </div>

            </div>
        </div>

        <?php startblock('footer') ?>
        <footer>
        <div class="container">
            <div class="row">
                <div class="col-4"><a href="http://bgo-bioimagerie.github.io/platformmanager/">Documentation</a></div>
                <div class="col-4"><a href="core/about">About</a></div>
                <div class="col-4"><a href="core/privacy">Privacy</a></div>
            </div>
        </div>
        </footer>
        <?php endblock() ?>

        <?php
        if($isdev) {
           echo $debugbarRenderer->render();
        }
        ?>

    <script src="externals/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>

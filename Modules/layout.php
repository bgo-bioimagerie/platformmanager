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

        <?php startblock('stylesheet') ?>
        <link rel="stylesheet" href="externals/bootstrap/css/bootstrap.min.css">
        <link rel="stylesheet" href="Modules/core/Theme/core.css">
        <?php endblock() ?>

    </head>
    <body style="background-color: #e7ecf0;">
        <?php startblock('navbar') ?>
        <?php endblock() ?>

        <?php startblock('spacenavbar') ?>
        <?php endblock() ?>
        <div id="app">
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

        <?php startblock('footer') ?>
        <?php endblock() ?>

        <?php
        if($isdev) {
            echo $debugbarRenderer->render();
        }
        ?>
    </body>
</html>

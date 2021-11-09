<!doctype html>
<?php require_once 'Modules/layout.php' ?>

<!-- header -->
<?php startblock('title') ?>
Platform-Manager
<?php endblock() ?>



<?php startblock('stylesheet') ?>
<link rel="stylesheet" href="externals/bootstrap/css/bootstrap.min.css">
<?php
$headless = Configuration::get("headless");
if (!$headless) {
    ?>
    <link href="externals/core/theme/navbar-fixed-top.css" rel="stylesheet">
    <?php
}
?>
<link rel="stylesheet" href="Modules/core/Theme/core.css">
<link href="externals/datepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/spacemenu.css' />
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/space.css' />
<?php endblock() ?> 



<?php
startblock('navbar');
require_once 'Modules/core/Controller/CorenavbarController.php';
$navController = new CorenavbarController(new Request(array(), false));
echo $navController->navbar();
?>

<?php
endblock();
?>


<?php startblock('spacenavbar'); ?>
<div class="col-md-12 pm-space-navbar">
    <?php
    require_once 'Modules/core/Controller/CorespaceController.php';
    $spaceController = new CorespaceController(new Request(array(), false));
    echo $spaceController->navbar($id_space);

    $modelCoreConfig = new CoreConfig();
    $showNavBarEstore = 1; //$modelCoreConfig->getParam("showNavBarEstore", $id_space);
    ?>
</div> 
<div class="col-md-12" style="margin-top: 7px; margin-bottom: -14px;">
    <?php
    if (isset($_SESSION["message"])) {
        ?>
        <div class="alert alert-info">
            <?php echo $_SESSION["message"] ?>
        </div>
        <?php
        unset($_SESSION["message"]);
    }
    ?>
</div>
<div class="col-md-12">
    <?php if ($showNavBarEstore) { ?>
        <div class="col-md-2 col-xs-12" style="padding-left: 0px; padding-right: 0px;">
            <?php
            require_once 'Modules/estore/Controller/EstoreController.php';
            $menucontroller = new EstoreController(new Request(array(), false));
            echo $menucontroller->navbar($id_space);
            ?>
        </div>
    <?php } ?>

    <div class="col-md-10 col-xs-12">


        <?php endblock(); ?>

        <?php startblock('content') ?>
        <?php endblock() ?>    

        <?php startblock('footer') ?>
    </div>

</div>
</div>
<?php
endblock();

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
$pmspaceheadercontent = "";
$pmspaceheadernavbar = "pm-space-navbar-no-header";
if (!$headless) {
    $pmspaceheadercontent = "pm-space-content";
    $pmspaceheadernavbar = "pm-space-navbar";
    ?>
    <link href="data/core/theme/navbar-fixed-top.css" rel="stylesheet">
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
if (!$headless) {
    require_once 'Modules/core/Controller/CorenavbarController.php';
    $navController = new CorenavbarController(new Request(array(), false));
    echo $navController->navbar();
}
endblock();
?>


<?php startblock('spacenavbar'); ?>
<?php
if (!$headless) {
    require_once 'Modules/core/Controller/CorespaceController.php';
    $spaceController = new CorespaceController(new Request(array(), false));
    echo $spaceController->navbar($id_space);
}
?>
<div class="col-md-2 col-lg-2 <?php echo $pmspaceheadernavbar ?>" >
    <?php
    require_once 'Modules/antibodies/Controller/AntibodiesController.php';
    $menucontroller = new AntibodiesController(new Request(array(), false));
    echo $menucontroller->navbar($id_space);
    ?>
</div>
<div class="col-md-10 col-lg-10 <?php echo $pmspaceheadercontent ?>" >
    <?php endblock(); ?>



    <?php startblock('content') ?>
    <?php endblock() ?>




    <?php startblock('footer') ?>
</div>

<?php
endblock();

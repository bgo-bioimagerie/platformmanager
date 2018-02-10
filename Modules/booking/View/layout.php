<!doctype html>
<?php require_once 'Modules/layout.php' ?>

<!-- header -->
    <?php startblock('title') ?>
    Core - Platform-Manager
    <?php endblock() ?>
        
    <?php startblock('stylesheet') ?>
    <link rel="stylesheet" href="externals/bootstrap/css/bootstrap.min.css">
    <?php
$headless = Configuration::get("headless");
if (!$headless) {
    ?>
    <link href="data/core/theme/navbar-fixed-top.css" rel="stylesheet">
    <?php
}
?>
    <link rel="stylesheet" href="Modules/core/Theme/core.css">
    <link href="externals/datepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
    <link rel='stylesheet' type='text/css' href='Modules/core/Theme/spacemenu.css' />
    
    <?php endblock() ?>
            
<!-- content -->

    <?php startblock('navbar') ?>
    <?php
    if(!$headless){
        require_once 'Modules/core/Controller/CorenavbarController.php';
        $navController = new CorenavbarController(new Request(array(), false));
        echo $navController->navbar();
    }
    
    ?>
    <?php endblock(); ?>
    
    
    <?php startblock('spacenavbar'); ?>
<?php
require_once 'Modules/core/Controller/CorespaceController.php';
$spaceController = new CorespaceController(new Request(array(), false));
echo $spaceController->navbar($id_space);
?>
<div class="row" style="margin-top: 55px">
    <?php include 'Modules/booking/View/navbarbooking.php'; ?>
</div>
    <?php endblock();

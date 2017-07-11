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
if (!$headless) {
    require_once 'Modules/core/Controller/CorenavbarController.php';
    $nullrequest = new Request(array(), false);
    $navController = new CorenavbarController($nullrequest);
    echo $navController->navbar();
}
?>
<?php endblock() ?>

<?php startblock('content') ?>
<?php
endblock();

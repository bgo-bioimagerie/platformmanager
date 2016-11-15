<!doctype html>
<?php require_once 'Modules/core/View/layout.php' ?>

<!-- header -->
    <?php startblock('title') ?>
    Database - Platform-Manager
    <?php endblock() ?>
        
    <?php startblock('stylesheet') ?>
    <link rel="stylesheet" href="externals/bootstrap/css/bootstrap.min.css">
    <link href="data/core/theme/navbar-fixed-top.css" rel="stylesheet">
    <link rel="stylesheet" href="Modules/core/Theme/core.css">
    <link href="externals/datepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
    <?php endblock() ?>
            
<!-- content -->

    <?php startblock('navbar') ?>
    <?php
        require_once 'Modules/core/Controller/CorenavbarController.php';
        $navController = new CorenavbarController();
        echo $navController->navbar();
    ?>

    <?php include 'Modules/core/View/spacebar.php'; ?>
    <?php include 'Modules/database/View/navbar.php'; ?>
    <?php endblock(); ?>

    <?php startblock('content') ?>
    <?php endblock();

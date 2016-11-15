<!doctype html>
<?php require_once 'Modules/core/View/layout.php' ?>

<!-- header -->
    <?php startblock('title') ?>
    Testapi - Platform-Manager
    <?php endblock() ?>
        
    <?php startblock('stylesheet') ?>
    <link rel="stylesheet" href="externals/bootstrap/css/bootstrap.min.css">
    <link href="data/core/theme/navbar-fixed-top.css" rel="stylesheet">
    <link rel="stylesheet" href="Modules/core/Theme/core.css">
    <link href="externals/datepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
    <link rel='stylesheet' type='text/css' href='Modules/core/Theme/spacemenu.css' />
    <?php endblock() ?>
            
    <?php startblock('navbar') ?>
    <?php
    require_once 'Modules/core/Controller/CorenavbarController.php';
    $navController = new CorenavbarController();
    echo $navController->navbar();
    
    ?> 
    <?php include 'Modules/core/View/spacebar.php'; ?>
    <?php include 'Modules/antibodies/View/navbar.php'; ?>
    <?php endblock(); ?>
<!-- content -->
    <?php startblock('content') ?>
    <?php endblock();

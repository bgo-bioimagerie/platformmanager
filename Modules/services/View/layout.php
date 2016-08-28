<!doctype html>
<?php require_once 'Modules/layout.php' ?>

<!-- header -->
    <?php startblock('title') ?>
    Core - Platform-Manager
    <?php endblock() ?>
        
    <?php startblock('stylesheet') ?>
    <link rel="stylesheet" href="externals/bootstrap/css/bootstrap.min.css">
    <link href="data/core/theme/navbar-fixed-top.css" rel="stylesheet">
    <link rel="stylesheet" href="Modules/core/Theme/core.css">
    <link href="externals/datepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
    
     <style>
.bs-glyphicons{margin:10px -10px 0px 0px;overflow:hidden}
.bs-glyphicons-list{padding-left:0;list-style:none}
.bs-glyphicons li{float:left;width:100%;height:30px;padding-left:10px;
font-size:10px;line-height:1.4;text-align:center;background-color:#f1f1f1;border:0px solid #fff}

.bs-glyphicons .glyphicon{margin-top:5px;margin-bottom:10px;font-size: 14px; color: #000;}
.bs-glyphicons .glyphicon-class{display:block;text-align:left;word-wrap:break-word}

.bs-glyphicons li:hover{color:#fff;background-color:#337ab7}@media (min-width:768px){
.bs-glyphicons{margin-right:0;margin-left:0}
.bs-glyphicons li{width:100%;font-size:10px}
}

.bs-glyphicons li a{color:#888888;}
.bs-glyphicons li a:hover{color:#fff;}
.bs-glyphicons .glyphicon-class:hover{color:#fff;}

</style>
    <?php endblock() ?>
            
<!-- content -->

    <?php startblock('navbar') ?>
    <?php
    require_once 'Modules/core/Controller/CorenavbarController.php';
    $navController = new CorenavbarController();
    echo $navController->navbar();
    
    
    ?> 
    <?php include 'Modules/core/View/spacebar.php'; ?>
    <?php include 'Modules/services/View/navbar.php'; ?>
    <?php endblock();

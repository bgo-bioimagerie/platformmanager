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
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/space.css' />
<?php endblock() ?> 



<?php
startblock('navbar');
if (!$headless) {
    require_once 'Modules/core/Controller/CorenavbarController.php';
    $nullrequest = new Request(array(), false);
    $navController = new CorenavbarController($nullrequest);
    echo $navController->navbar();
}
endblock();
?>


    <?php startblock('spacenavbar'); ?>
<div class="col-md-2 pm-space-navbar">
    <?php
    require_once 'Modules/core/Controller/CorespaceController.php';
    $nullrequest = new Request(array(), false);
    $spaceController = new CorespaceController($nullrequest);
    echo $spaceController->navbar($id_space);
    ?>
</div> 
<div class="col-md-8">
<?php endblock(); ?>



    <?php startblock('content') ?>
<?php endblock() ?>




<?php startblock('footer') ?>
</div>
<div class="col-md-2 pm-space-navbar-right">
    <nav class="navbar navbar-default sidebar" style="border: 1px solid #f1f1f1;" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header" style="background-color: #e1e1e1;">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-sidebar-navbar-collapse-1">
                    <span class="sr-only">Toggle navigation</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>      
            </div>
            <div class="collapse navbar-collapse"  id="bs-sidebar-navbar-collapse-1">
                <ul class="nav navbar-nav" >
                    <li>
                        <a style="background-color:<?php echo $space["color"]; ?>; color: #fff; margin-left: -20px; margin-right: -70px" href=""> <?php echo CoreTranslator::Dashboard($lang) ?> <span style="font-size:16px;" class="pull-right hidden-xs showopacity glyphicon glyphicon-th"></span></a>
                    </li>
                    <ul class="pm-nav-li">
                        <li >
                            <div class="inline pm-inline-div">
                                <a href="spacedashboard/<?php echo $id_space ?>"><?php echo CoreTranslator::Activation($lang) ?></a>
                            </div>
                        </li>
                        <br/>    
                        <li>
                            <div class="inline pm-inline-div">
                                <a href="spacedashboardsections/<?php echo $id_space ?>"><?php echo CoreTranslator::Sections($lang) ?></a>
                            </div>
                        </li>

                        <li>
                            <div class="inline pm-inline-div">
                                <a href="spacedashboarditems/<?php echo $id_space ?>"><?php echo CoreTranslator::Items($lang) ?></a>
                            </div>
                        </li>
                    </ul>
                </ul>
            </div>
        </div>
    </nav>
</div>
<?php
endblock();

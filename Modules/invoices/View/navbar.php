<!doctype html>
<?php require_once 'Modules/layout.php' ?>

<!-- header -->
<?php startblock('title') ?>
Platform-Manager
<?php endblock() ?>



<?php startblock('stylesheet') ?>
<link rel="stylesheet" href="externals/bootstrap/css/bootstrap.min.css">
<link href="externals/core/theme/navbar-fixed-top.css" rel="stylesheet">
<link rel="stylesheet" href="Modules/core/Theme/core.css">
<link href="externals/datepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/spacemenu.css' />
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/space.css' />
<?php endblock() ?> 



<?php startblock('navbar'); 
require_once 'Modules/core/Controller/CorenavbarController.php';
require_once 'Modules/core/Controller/CorespaceController.php';
$navController = new CorenavbarController(new Request(array(), false));
echo $navController->navbar();
 endblock(); ?>


<?php startblock('spacenavbar'); ?>
<div class="col-md-2 pm-space-navbar">
<?php
require_once 'Modules/core/Controller/CorespaceController.php';
$spaceController = new CorespaceController(new Request(array(), false));
echo $spaceController->navbar($id_space);
?>
</div> 
<div class="col-md-10">
<?php
endblock(); ?>



<?php startblock('content') ?>
    <?php endblock() ?>
    



<?php startblock('footer') ?>
</div>
<?php endblock();
    
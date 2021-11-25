<!doctype html>
<?php require_once 'Modules/layout.php' ?>
<?php require_once 'Modules/core/Controller/CorespaceController.php' ?>


<?php startblock('stylesheet') ?>
<link href="externals/datepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/spacemenu.css' />
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/space.css' />
<?php endblock() ?> 

<?php startblock('spacenavbar'); ?>
<?php
if($id_space) {
//require_once 'Modules/core/Controller/CorespaceController.php';
$nullrequest = new Request(array(), false);
$spaceController = new CorespaceController($nullrequest);
echo $spaceController->navbar($id_space);
}
?>
</div> 
<?php
-endblock(); ?>



<?php startblock('content') ?>
<?php endblock() ?>
    


    
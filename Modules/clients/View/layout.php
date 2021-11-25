<!doctype html>
<?php require_once 'Modules/layout.php' ?>


<?php startblock('stylesheet') ?>
<link href="externals/datepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/spacemenu.css' />
<?php endblock() ?> 




<?php startblock('spacenavbar'); ?>
<?php
if($id_space) {
    require_once 'Modules/core/Controller/CorespaceController.php';
    $nullrequest = new Request(array(), false);
    $spaceController = new CorespaceController($nullrequest);
    echo $spaceController->navbar($id_space);
}
?>
<?php endblock(); ?>


<?php startblock('spacemenu'); ?>
<div class="col-md-2 col-lg-2" >
    <?php
    require_once 'Modules/clients/Controller/ClientsController.php';
    $menucontroller = new ClientsController(new Request(array(), false));
    echo $menucontroller->navbar($id_space);
    ?>
</div>
<?php endblock(); ?>


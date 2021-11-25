<!doctype html>
<?php require_once 'Modules/layout.php' ?>





<?php startblock('stylesheet') ?>
<link href="externals/datepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/spacemenu.css' />

<?php endblock() ?> 



<?php startblock('spacenavbar'); ?>
<?php
    require_once 'Modules/core/Controller/CorespaceController.php';
    $spaceController = new CorespaceController(new Request(array(), false));
    echo $spaceController->navbar($id_space);
?>

<?php endblock(); ?>




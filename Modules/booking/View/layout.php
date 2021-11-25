<!doctype html>
<?php require_once 'Modules/layout.php' ?>

<!-- header -->
    <?php startblock('title') ?>
    Core - Platform-Manager
    <?php endblock() ?>
        
    <?php startblock('stylesheet') ?>
    <link href="externals/datepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">    
    <?php endblock() ?>
            
<!-- content -->

    
<?php startblock('spacenavbar'); ?>
    <?php
        require_once 'Modules/core/Controller/CorespaceController.php';
        $spaceController = new CorespaceController(new Request(array(), false));
        echo $spaceController->navbar($id_space);

    ?>
<?php endblock(); ?>

<?php startblock('spacemenu'); ?>
        <div class="col-sm-12">
            <?php include 'Modules/booking/View/navbarbooking.php'; ?>
        </div>
<?php endblock(); ?>



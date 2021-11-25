<!doctype html>
<?php require_once 'Modules/layout.php' ?>




<?php startblock('spacenavbar'); ?>
<?php
    require_once 'Modules/core/Controller/CorespaceController.php';
    $spaceController = new CorespaceController(new Request(array(), false));
    echo $spaceController->navbar($id_space);

?>
<?php endblock(); ?>

<?php startblock('spacemenu'); ?>
<div class="col-md-2 col-lg-2" >
<?php
    require_once 'Modules/services/Controller/ServicesController.php';
    $menucontroller = new ServicesController(new Request(array(), false));
    echo $menucontroller->navbar($id_space);
    ?>
</div>
<?php endblock(); ?>



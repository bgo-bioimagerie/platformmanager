<?php include_once 'Modules/resources/View/layout.php' ?>

    
<?php startblock('content') ?>
    <div class="pm-form">

        <?php include_once "Modules/resources/View/Resourcesinfo/edittabs.php" ?>
        <div class="col-10"><p></p></div>
                <?php echo $formHtml ?>
    </div>
<?php endblock(); ?>

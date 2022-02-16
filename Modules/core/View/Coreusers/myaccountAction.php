<?php include 'Modules/core/View/layout.php' ?>


<?php startblock('spacenavbar') ?>
    <?php include( "Modules/core/View/Coreusers/navbar.php" ); ?>
<?php endblock() ?>

    
<?php startblock('content') ?>
    <div class="container pm-form">
        <?php echo $formHtml ?>
    </div>
<?php endblock(); ?>

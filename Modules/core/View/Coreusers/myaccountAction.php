<?php include 'Modules/core/View/layout.php' ?>


<!-- body -->     
<?php startblock('content') ?>

<?php include( "Modules/core/View/Coreusers/navbar.php" ); ?>

<div class="col-md-12" style="margin-top:50px;">
    <div class="container pm-form">
        <?php echo $formHtml ?>
    </div>
</div> <!-- /container -->
<?php
endblock();

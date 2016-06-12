<?php include 'Modules/resources/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
    
    <div class="col-xs-12 col-md-12">

    <?php include "Modules/resources/View/Resources/edittabs.php" ?>
    <div class="col-xs-12"><p></p></div>
    
    
    <div class="col-xs-12 col-md-6 col-md-offset-1" id="pm-form">
        <?php echo $formEvent ?>
    </div>
    <div class="col-xs-12 col-md-3 col-md-offset-1" id="pm-form">
        <?php echo $formEvent ?>
    </div>
</div>

<?php endblock();
<?php include_once 'Modules/resources/View/layout.php' ?>

    
<?php startblock('content') ?>
    
<div class="pm-content">

    <?php include "Modules/resources/View/Resourcesinfo/edittabs.php" ?>
    <div class="col-12"><p></p></div>
    
    
    <div class="col-12 pm-form">
        <?php echo $formEvent ?>
    </div>
    
    <?php if ($id_event > 0) { ?>
    <div class="col-12">
        <div class="col-12 pm-form">
            <?php echo $formDownload ?>
        </div>
        <div class="col-12 pm-table">
            <?php echo $filesTable ?>
        </div>
    </div>
    <?php } ?>
</div>

<?php endblock(); ?>
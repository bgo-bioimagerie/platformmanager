<?php include_once 'Modules/core/View/spacelayout.php' ?>

    
<?php startblock('content') ?>
    
<div class="pm-content">

    <div class="col-10"><p></p></div>
    
    
    <div class="col-10 col-md-7" id="pm-form">
        <?php echo $formEvent ?>
    </div>
    
    <?php if ($id_event > 0) { ?>
    <div class="col-10 col-md-5">
        <div id="pm-table">
            <?php echo $filesTable ?>
        </div>
    </div>
    <?php } ?>
</div>

<?php endblock(); ?>
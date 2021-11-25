<?php include 'Modules/core/View/spacelayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
    
    <div class="col-md-10 pm-content">

    <div class="col-xs-10"><p></p></div>
    
    
    <div class="col-xs-10 col-md-7" id="pm-form">
        <?php echo $formEvent ?>
    </div>
    
    <?php if ($id_event > 0){ ?>
    <div class="col-xs-10 col-md-5">
        <div class="col-xs-10" id="pm-table">
            <?php echo $filesTable ?>
        </div>
    </div>
    <?php } ?>
</div>

<?php endblock();
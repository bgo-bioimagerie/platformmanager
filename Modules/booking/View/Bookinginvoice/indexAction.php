<?php include 'Modules/invoices/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-table">
    <h3><?php echo BookingTranslator::Invoice_booking($lang) ?> </h3>
    <div class="row">
        <div class="col-10">
            <?php echo $formByProjects ?>
        </div> 
    </div>
    <div class="row">
        <div class="col-10">
            <?php echo $formByPeriod ?>
        </div>
    </div>
        
</div>

<?php endblock(); ?>
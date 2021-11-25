<?php include 'Modules/invoices/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-10 pm-table">
    <h3><?php echo BookingTranslator::Invoice_booking($lang) ?> </h3>
    <div class="row">
        <div class="col-md-10">
            <?php echo $formByProjects ?>
        </div> 
    </div>
    <div class="row">
        <div class="col-md-10">
            <?php echo $formByPeriod ?>
        </div>
    </div>
        
</div>

<?php endblock();
<?php include 'Modules/invoices/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-12 pm-table">
    <h3><?php echo InvoicesTranslator::NewInvoice($lang) ?> </h3>
    
    <div class="col-md-12">
        <?php echo $formAll ?>
    </div>

    <div class="col-md-12">
        <?php echo $formByPeriod ?>
    </div>

</div>

<?php
endblock();

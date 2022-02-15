<?php include 'Modules/invoices/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-table row">
    <h3><?php echo InvoicesTranslator::NewInvoice($lang) ?> </h3>
    
    <div class="col-md-12">
        <?php echo $formAll ?>
    </div>

    <div class="col-md-12">
        <?php echo $formByPeriod ?>
    </div>

</div>

<?php endblock(); ?>

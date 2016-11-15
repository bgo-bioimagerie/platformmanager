<?php include 'Modules/invoices/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-10" id="pm-form">
    <h3><?php echo ServicesTranslator::Invoice_project($lang) ?> </h3>
    <div class="col-md-12">
        <?php echo $formByProjects ?>
    </div> 
    <div class="col-md-12">
        <?php echo $formByPeriod ?>
    </div>
        
</div>

<?php endblock();
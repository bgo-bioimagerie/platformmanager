<?php include_once 'Modules/invoices/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-form row">
    <h3><?php echo ServicesTranslator::Invoice_project($lang) ?> </h3>
    <div class="col-12">
        <?php echo $formByProjects ?>
    </div> 
    <div class="col-12">
        <?php echo $formByPeriod ?>
    </div>
        
</div>

<?php endblock(); ?>
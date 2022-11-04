<?php include_once 'Modules/clients/View/layout.php' ?>

    
<?php startblock('content') ?>
<div class="container pm-table">
<div class="page-header">
    <h3><?php echo ClientsTranslator::Pricings($lang) ?></h3>
</div>    
    <?php echo $tableHtml ?>
</div>
<?php endblock(); ?>

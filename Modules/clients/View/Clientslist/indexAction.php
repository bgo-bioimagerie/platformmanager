<?php include 'Modules/clients/View/layout.php' ?>

    
<?php startblock('content') ?>
<div class="pm-table">
<div class="page-header">
    <h3><?php echo ClientsTranslator::Clients($lang) ?></h3>
</div>    
    <?php echo $tableHtml ?>
</div>
<?php endblock(); ?>

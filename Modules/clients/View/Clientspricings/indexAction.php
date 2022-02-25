<?php include 'Modules/clients/View/layout.php' ?>

    
<?php startblock('content') ?>
<div class="pm-table">
<div class="page-header">
    <h3><?php echo ClientsTranslator::Pricings($lang) ?></h3>
</div>    
<a style="margin:10px" class="btn btn-default" href="clpricingedit/<?php echo $id_space ?>/0"><?php echo ClientsTranslator::NewPricing($lang) ?></a>
    <?php echo $tableHtml ?>
</div>
<?php endblock(); ?>

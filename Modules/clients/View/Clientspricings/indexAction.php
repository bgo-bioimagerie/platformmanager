<?php include 'Modules/clients/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-10 pm-table">
<div class="page-header">
    <h3><?php echo ClientsTranslator::Pricings($lang) ?></h3>
</div>    
<a class="btn btn-default" href="clpricingedit/<?php echo $id_space ?>/0"><?php echo ClientsTranslator::NewPricing($lang) ?></a>
    <?php echo $tableHtml ?>
</div>
<?php
endblock();

<?php include 'Modules/clients/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-12 pm-table">
<div class="page-header">
    <h3><?php echo BreedingTranslator::Pricings($lang) ?></h3>
</div>    
<a class="btn btn-default" href="clpricingedit/<?php echo $id_space ?>"><?php echo BreedingTranslator::NewPricing($lang) ?></a>
    <?php echo $tableHtml ?>
</div>
<?php
endblock();

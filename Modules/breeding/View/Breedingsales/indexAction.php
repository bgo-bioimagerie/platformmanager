<?php include 'Modules/breeding/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-12 pm-table">
<div class="page-header">
    <h3><?php echo BreedingTranslator::Sales($lang) ?></h3>
</div>    
    <a class="btn btn-default" href="brsalenew/<?php echo $id_space ?>"><?php echo BreedingTranslator::NewSale($lang) ?></a>
    <?php echo $tableHtml ?>
</div>
<?php
endblock();

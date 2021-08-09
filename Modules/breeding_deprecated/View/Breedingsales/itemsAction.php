<?php include 'Modules/breeding/View/salelayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-12">
<div class="page-header">
    <h3><?php echo BreedingTranslator::Details($lang) ?></h3>
</div>    
<a class="btn btn-default" href="brsaleitemedit/<?php echo $id_space ?>/<?php echo $id_sale ?>/0"><?php echo BreedingTranslator::_New($lang) ?></a>
    <?php echo $tableHtml ?>
</div>
<?php
endblock();
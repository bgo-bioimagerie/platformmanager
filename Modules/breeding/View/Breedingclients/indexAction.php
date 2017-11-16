<?php include 'Modules/breeding/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-12 pm-table">
<div class="page-header">
    <h3><?php echo BreedingTranslator::Clients($lang) ?></h3>
</div>    
<a class="btn btn-default" href="brclientedit/<?php echo $id_space ?>"><?php echo BreedingTranslator::NewClient($lang) ?></a>
    <?php echo $tableHtml ?>
</div>
<?php
endblock();

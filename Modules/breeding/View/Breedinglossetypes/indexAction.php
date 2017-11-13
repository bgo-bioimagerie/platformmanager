<?php include 'Modules/breeding/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-12 pm-table">
<div class="page-header">
    <h3><?php echo BreedingTranslator::lossetypes($lang) ?></h3>
</div>    
<a class="btn btn-default" href="brlossetypeedit/<?php echo $id_space ?>"><?php echo BreedingTranslator::NewLosseType($lang) ?></a>
    <?php echo $tableHtml ?>
</div>

<?php
endblock();

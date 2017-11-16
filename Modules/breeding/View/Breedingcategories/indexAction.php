<?php include 'Modules/breeding/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-12 pm-table">
<div class="page-header">
    <h3><?php echo BreedingTranslator::CategoriesProduct($lang) ?></h3>
</div>    
<a class="btn btn-default" href="brproductcategoryedit/<?php echo $id_space ?>"><?php echo BreedingTranslator::NewCategoryProduct($lang) ?></a>
    <?php echo $tableHtml ?>
</div>
<?php
endblock();

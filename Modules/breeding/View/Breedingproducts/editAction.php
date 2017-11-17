<?php include 'Modules/breeding/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-12 pm-form">
    <?php echo $formHtml ?>


    <div class="page-header"><h3><?php echo BreedingTranslator::Stages($lang) ?></h3></div>
    <a class="btn btn-default" href="brproductstageedit/<?php echo $id_space ?>/<?php echo $id_product ?>/0"><?php echo BreedingTranslator::NewStage($lang) ?></a>
    <div class="col-md-12">
        <?php echo $tableHtml ?>
    </div>
</div>
<?php
endblock();

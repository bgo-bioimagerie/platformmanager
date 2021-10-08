<?php include 'Modules/breeding/View/batchlayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
    <div class="col-md-12" >
        
        <div class="page-header">
            <h3><?php echo BreedingTranslator::Treatments($lang) ?><h3>
        </div>
        <a class="btn btn-default" href="brtreatmentedit/<?php echo $id_space ?>/<?php echo $batch["id"] ?>/0"><?php echo BreedingTranslator::NewTreatment($lang) ?></a>
        <?php echo $tableHtml ?>
    </div>
<?php
endblock();

<?php include 'Modules/breeding/View/batchlayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<a class="btn btn-primary" href="brlosseedit/<?php echo $id_space ?>/<?php echo $id_batch ?>/0"><?php echo BreedingTranslator::NewLosse($lang) ?></a>

<div class="page-header">
    <h3><?php echo BreedingTranslator::Moves($lang) ?></h3>
</div>
        <?php echo $tableHtml ?>
<?php
endblock();

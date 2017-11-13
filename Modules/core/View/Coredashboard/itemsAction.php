<?php include 'Modules/core/View/Coredashboard/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-12 pm-table">
    
    <div class="page-header">
        <h3><?php echo CoreTranslator::Items($lang) ?></h3>
    </div>
    <a class="btn btn-default" href="spacedashboarditemedit/<?php echo $id_space ?>/0"><?php echo CoreTranslator::NewItem($lang) ?></a>
    
    <?php echo $tableHtml ?>
</div>
<?php endblock();
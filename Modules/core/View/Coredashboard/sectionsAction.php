<?php include 'Modules/core/View/Coredashboard/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-12 pm-table">
    
    <div class="page-header">
        <h3><?php echo CoreTranslator::Sections($lang) ?></h3>
    </div>
    <a class="btn btn-default" href="spacedashboardsectionedit/<?php echo $id_space ?>/0"><?php echo CoreTranslator::NewSection($lang) ?></a>
    
    <?php echo $tableHtml ?>
</div>
<?php endblock();
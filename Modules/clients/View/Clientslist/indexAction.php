<?php include 'Modules/clients/View/layout.php' ?>

    
<?php startblock('content') ?>
<div class="pm-table">
<div class="page-header">
    <h3><?php echo ClientsTranslator::Clients($lang) ?></h3>
</div>    
<a class="btn btn-outline-dark" href="clclientedit/<?php echo $id_space ?>"><?php echo ClientsTranslator::NewClient($lang) ?></a>
    <?php echo $tableHtml ?>
</div>
<?php endblock(); ?>

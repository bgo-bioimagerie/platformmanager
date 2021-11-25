<?php include 'Modules/clients/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-10 pm-table">
<div class="page-header">
    <h3><?php echo ClientsTranslator::Clients($lang) ?></h3>
</div>    
<a class="btn btn-default" href="clclientedit/<?php echo $id_space ?>"><?php echo ClientsTranslator::NewClient($lang) ?></a>
    <?php echo $tableHtml ?>
</div>
<?php
endblock();

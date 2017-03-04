<?php include 'Modules/core/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-2 pm-nav">
<?php include('Modules/core/View/Coremenus/navbar.php'); ?>
</div>
<div class="col-md-10 pm-form">
    <?php echo $tableHtml ?>
</div>
<?php endblock();
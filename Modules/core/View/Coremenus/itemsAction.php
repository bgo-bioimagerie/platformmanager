<?php include 'Modules/core/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<?php include('Modules/core/View/Coremenus/navbar.php'); ?>

<div class="container">
    <?php echo $tableHtml ?>
</div>
<?php endblock();
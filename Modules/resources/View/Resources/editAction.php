<?php include 'Modules/resources/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="container">
    
    <?php include "Modules/resources/View/Resources/edittabs.php" ?>
    <div class="col-xs-12"><p></p></div>
    <?php echo $formHtml ?>
</div>

<?php endblock();
<?php include 'Modules/breeding/View/batchlayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="page-header">
    <h3><?php echo BreedingTranslator::EditLosse($lang)?> </h3>
</div>
<div class="col-md-12">
    <?php
    if (isset($_SESSION["message"])) {
        ?>
        <div class="alert alert-info">
            <?php echo $_SESSION["message"] ?>
        </div>
        <?php
        unset($_SESSION["message"]);
    }
    ?>
</div>
    <div class="col-md-12">
        <?php echo $formHtml ?>
    </div>
<?php
endblock();
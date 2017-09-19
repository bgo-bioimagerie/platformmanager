<?php include 'Modules/core/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-8 col-md-offset-2 pm-form">
    <?php if (isset($_SESSION["message"])) {
        ?>
        <div class="alert alert-success">
            <?php echo $_SESSION["message"] ?>
        </div>
        <?php
    }
    ?>

    <?php echo $formHtml ?>
</div>
<?php
endblock();

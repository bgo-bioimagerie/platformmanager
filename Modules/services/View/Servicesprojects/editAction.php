<?php include 'Modules/services/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="pm-form">

    <div class="col-md-12">
        <?php
        if (isset($_SESSION["message"]) && $_SESSION["message"]) {
            ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION["message"] ?>
            </div>
            <?php
            unset($_SESSION["message"]);
        }
        ?>
    </div>

<?php echo $formHtml ?>
</div>

<?php
endblock();

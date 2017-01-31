<?php include 'Modules/core/View/layout.php' ?>

<!-- header -->

<!-- body -->     
<?php startblock('content') ?>

<?php
if (isset($_SESSION["message"])) {
    $pos = strpos($_SESSION["message"], "uccess");
    if ($pos === false) {
        ?>
        <div class="alert alert-danger">
            <?php echo $_SESSION["message"] ?>
        </div>
        <?php
    } else {
        ?>
        <div class="alert alert-success">
            <?php echo $_SESSION["message"] ?>
        </div>
        <?php
    }
}
?>


<div class="container" id="pm-table">
    <?php echo $formHtml ?>
</div>
<?php
endblock();

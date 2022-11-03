<?php include 'Modules/core/View/layout.php' ?>

<!-- header -->

    
<?php startblock('content') ?>

<?php
    if ($updateInfo["status"] == "error") {
        ?>
        <div class="alert alert-danger">
            <?php echo $updateInfo["message"] ?>
        </div>
        <?php
    } else {
        ?>
        <div class="alert alert-success">
            <?php echo $updateInfo["message"] ?>
        </div>
        <?php
    }
?>

<?php endblock(); ?>

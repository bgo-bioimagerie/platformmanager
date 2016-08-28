<?php include 'Modules/core/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<?php include('Modules/core/View/Coremenus/navbar.php'); ?>

<div class="container">
    
    <?php 
    if (isset($_SESSION["message"]) && $_SESSION["message"] != ""){ 
        ?>
        <div class="col-xs-12 col-md-10 col-md-offset-1" style="padding-top: 12px;" >
            <div class="alert alert-success" role="alert">
            <p><?php echo $_SESSION["message"] ?></p>
            </div>
        </div>
    <?php }
    $_SESSION["message"] = "";
    ?>
    
    <?php echo $formHtml ?>
</div>
<?php endblock();
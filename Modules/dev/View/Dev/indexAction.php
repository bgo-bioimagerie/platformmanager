<?php include 'Modules/dev/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="container">
    
        <?php 
    if (isset($_SESSION["message"]) && $_SESSION["message"] != ""){ 
        $message = $_SESSION["message"];?>
        <?php if (strpos($message, "Error")){ ?>
        <div class="col-xs-12 col-md-10 col-md-offset-1">
            <div class="alert alert-danger" role="alert">
            <p><?php echo  $message ?></p>
            </div>
        </div>
    <?php }else{ ?>
        <div class="col-xs-12 col-md-10 col-md-offset-1">
            <div class="alert alert-success" role="alert">
            <p><?php echo  $message ?></p>
            </div>
        </div>
    <?php }
    $_SESSION["message"] = "";
    } ?>
    
    <div class="col-xs-12 col-md-10 col-md-offset-1">
        <?php echo $htmlForm ?>
    </div>
</div>

<?php endblock();
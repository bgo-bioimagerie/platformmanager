<?php include 'Modules/core/View/layout.php' ?>
 
<!-- body -->     
<?php startblock('content') ?>
<div class="container">
        <div class="col-md-12" id="pm-table">
    <div class="col-md-12">
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
    </div>
    <div class="col-md-12">
    <?php echo $formHtml ?>
    </div>
</div>
</div>
<?php
endblock();
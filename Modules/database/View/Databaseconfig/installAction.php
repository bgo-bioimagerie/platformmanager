<?php include 'Modules/database/View/configlayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-10 col-xs-12" id="pm-form">
    <?php include 'Modules/database/View/configmenu.php'; ?> 

<div class="col-xs-12 col-md-12" id="pm-form" style="background-color: #fff; min-height: 2000px;">
    
    <?php 
    if (isset($_SESSION["message"]) && $_SESSION["message"] != ""){ 
        $message = $_SESSION["message"];?>
        <?php if (strpos($message, "Error")){ ?>
        <div class="col-xs-12 col-md-10 col-md-offset-1" style="background-color: #fff;">
            <div class="alert alert-danger" role="alert">
            <p><?php echo  $message ?></p>
            </div>
        </div>
    <?php }else{ ?>
        <div class="col-xs-12 col-md-10 col-md-offset-1" style="background-color: #fff;">
            <div class="alert alert-success" role="alert">
            <p><?php echo  $message ?></p>
            </div>
        </div>
    <?php }
    $_SESSION["message"] = "";
    } ?>
    
    <?php foreach($forms as $form){ ?>
    <div class="col-xs-12 col-md-10 col-md-offset-1" style="height: 7px;">
        <p></p>
    </div>
    <div class="col-xs-12 col-md-10 col-md-offset-1"  id="pm-form" style="background-color: #fff; border-radius: 7px; padding: 7px;">
        <?php echo $form ?>
    </div>
    <?php } ?>
</div>
</div>

<?php endblock();
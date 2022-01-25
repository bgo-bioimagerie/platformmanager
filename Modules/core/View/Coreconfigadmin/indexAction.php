<?php include 'Modules/core/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="row">
    
    <div class="col-xs-12 col-md-10 col-md-offset-1">
        <h1><?php echo CoreTranslator::Core_configuration($lang) ?></h1>
    </div>
    
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
    
    <?php foreach($forms as $form){ ?>
    <div class="col-xs-12 col-md-10 col-md-offset-1" style="height: 7px;">
        <p></p>
    </div>
    <div class="col-xs-12 col-md-10 col-md-offset-1 pm-form-short">
        <?php echo $form ?>
    </div>
    <?php } ?>
</div>

<?php endblock(); ?>
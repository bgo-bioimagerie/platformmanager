<?php include 'Modules/booking/View/layoutsettings.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-12 pm-table">
    
    <?php 
    if (isset($_SESSION["message"]) && $_SESSION["message"] != "") {
        $dismissible = false;
        if (array_key_exists("dismissible", $_SESSION["message"])) {
            $dismissible = $_SESSION["message"]["dismissible"];
        }
?>
        <div class="row">
            <div class="col-xs-12 col-md-10 col-md-offset-1" style="padding-top: 12px;" >
                <div class="alert <?php echo $_SESSION["message"]["type"] ?>" role="alert">
                    <?php echo $_SESSION["message"]["content"];
                        if ($dismissible) {
                    ?>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php }
    $_SESSION["message"] = "";
    ?>
    
    <?php echo $formHtml ?>
    
</div>
<?php endblock();
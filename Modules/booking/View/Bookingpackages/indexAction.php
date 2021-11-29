<?php include 'Modules/booking/View/layoutsettings.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="pm-form">


    <?php
    if (isset($_SESSION["message"]) && $_SESSION["message"] != "") {
        ?>
        <div class="col-xs-12 col-md-10 col-md-offset-1" style="padding-top: 12px;" >
            <div class="alert alert-success" role="alert">
                <p><?php echo $_SESSION["message"] ?></p>
            </div>
        </div>
        <?php
    }
    $_SESSION["message"] = "";
    ?>

    <div class="col-xs-12">
        <?php echo $formHtml ?>
    </div>

</div>
<?php
endblock();

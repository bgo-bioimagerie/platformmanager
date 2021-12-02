<?php include 'Modules/core/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="row">
    <div class="col-md-12 pm-nav">
        <?php include('Modules/core/View/Coremainmenu/navbar.php'); ?>
    </div>

    <div class="col-md-12">
        <div class="container pm-form" >
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

        <?php echo $formHtml ?>
        </div>
    </div>
</div>
<?php
endblock();

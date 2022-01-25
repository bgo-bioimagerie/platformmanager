<?php require_once 'Framework/ti.php' ?>
<?php include 'Modules/layout.php' ?>


<?php startblock('stylesheet') ?>

<meta http-equiv="X-XSS-Protection" content="1; mode=block">
<meta http-equiv="X-Content-Type-Options" content="nosniff">

<!-- Custom styles for this template -->
<link href="Modules/core/Theme/signin.css" rel="stylesheet">

<?php endblock() ?>

<!-- body -->
<?php startblock('content') ?>

<div class="row" style="background-color: #fff; height:100%">

        <!-- Title -->
        <div class="col-sm-10 col-sm-offset-1 text-center">
            <h1 class="text-center login-title"><?php echo $home_title ?></h1>
        </div>

        <!-- Message -->
        <div class="col-sm-10 col-sm-offset-1 text-center">
            <p></p>
            <h3 style="text-align:center;"><?php echo $home_message ?></h3>
            <p></p>
        </div>

        <!-- Message -->
        <div class="col-sm-10 col-sm-offset-1 text-center">
             <?php
        if (isset($_SESSION["message"]) && $_SESSION["message"]) {
            if (substr($_SESSION["message"], 0, 3) === "Err") {
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
            unset($_SESSION["message"]);
        }
        ?>
        </div>

         <!-- Form -->
        <div class="col-sm-10 col-sm-offset-1 text-center">
            <p></p>
            <?php echo $formHtml ?>
            <p></p>
        </div>

</div>
<?php endblock(); ?>

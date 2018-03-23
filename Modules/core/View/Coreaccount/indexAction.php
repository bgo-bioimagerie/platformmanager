<?php require_once 'Framework/ti.php' ?>
<?php include 'Modules/layout.php' ?>

<!-- header -->
<?php startblock('title') ?>
Platform-Manager
<?php endblock() ?> 
<?php startblock('stylesheet') ?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Security-Policy" content="default-src 'self'; script-src 'self'">
<meta http-equiv="X-Frame-Options" content="DENY">
<meta http-equiv="X-XSS-Protection" content="1; mode=block">
<meta http-equiv="X-Content-Type-Options" content="nosniff">

<!-- Bootstrap core CSS -->
<link href="externals/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<!-- Custom styles for this template -->
<link href="signin.css" rel="stylesheet">

<!-- Bootstrap core CSS -->
<script src="Modules/core/Themes/caroussel/ie-emulation-modes-warning.js"></script>
<link href="Modules/core/Themes/caroussel/carousel.css" rel="stylesheet"> 
<?php endblock() ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-12" style="background-color: #fff; height:100%">
    <div class="row">

        <!-- Title -->
        <div class="col-sm-12">
            <h1 class="text-center login-title"><?php echo $home_title ?></h1>
        </div>

        
        <!-- Message -->
        <div class="col-sm-10 col-sm-offset-1 text-center">
             <?php
        if (isset($_SESSION["message"])) {
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
</div>   
<?php
endblock();

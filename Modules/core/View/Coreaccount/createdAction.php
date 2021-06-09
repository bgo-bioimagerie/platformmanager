<?php require_once 'Framework/ti.php' ?>
<?php include 'Modules/layout.php' ?>

<!-- header -->
<?php startblock('title') ?>
Platform-Manager
<?php endblock() ?>
<?php startblock('stylesheet') ?>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<?php
if (getenv('PFM_MODE') != 'dev') {
  echo "<meta http-equiv=\"Content-Security-Policy\" content=\"default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'\">\n";
} else {
    echo "<meta http-equiv=\"Content-Security-Policy-Report-Only\" content=\"default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'\">\n";
}
?>
<meta http-equiv="X-XSS-Protection" content="1; mode=block">
<meta http-equiv="X-Content-Type-Options" content="nosniff">

<!-- Bootstrap core CSS -->
<link href="externals/bootstrap/css/bootstrap.min.css" rel="stylesheet">

<!-- Custom styles for this template -->
<link href="Modules/core/Theme/signin.css" rel="stylesheet">

<!-- Bootstrap core CSS -->
<script src="Modules/core/Theme/caroussel/ie-emulation-modes-warning.js"></script>
<link href="Modules/core/Theme/caroussel/carousel.css" rel="stylesheet"> 
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
            <div class="alert alert-success">
                <?php echo $message ?>
            </div>
        </div>

    </div>
</div>
<?php
endblock();

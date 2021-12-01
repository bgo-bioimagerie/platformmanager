<?php require_once 'Framework/ti.php' ?>
<?php include 'Modules/layout.php' ?>

<?php startblock('stylesheet') ?>
<meta charset="utf-8">
<?php
if (getenv('PFM_MODE') != 'dev') {
  echo "<meta http-equiv=\"Content-Security-Policy\" content=\"default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'\">\n";
} else {
    echo "<meta http-equiv=\"Content-Security-Policy-Report-Only\" content=\"default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'\">\n";
}
?>
<meta http-equiv="X-XSS-Protection" content="1; mode=block">
<meta http-equiv="X-Content-Type-Options" content="nosniff">

<!-- Custom styles for this template -->
<link href="Modules/core/Theme/signin.css" rel="stylesheet">

<?php endblock() ?>

<!-- body -->
<?php startblock('content') ?>

<div class="row" style="background-color: #fff; height:100%">
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
<?php
endblock();

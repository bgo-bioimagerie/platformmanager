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

<h3>PFM installation</h3>

<div>
    <ul>
        <li>Code version: <?php echo $data['tag'] ?></li>
        <li>DB version: <?php echo $data['db'] ?></li>
    </ul>
</div>


  

</div>

<?php
endblock();

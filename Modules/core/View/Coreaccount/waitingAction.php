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
        <div class="col-10 offset-1 text-center">
            <p></p>
            <?php echo $message ?>
            <p></p>
        </div>
</div>
<?php endblock(); ?>

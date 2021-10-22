<?php include 'Modules/core/View/layout.php' ?>

<?php startblock('stylesheet') ?>

<link rel="stylesheet" type="text/css" href="externals/bootstrap/css/bootstrap.min.css">
<?php
$headless = Configuration::get("headless");
if (!$headless) {
    ?>
    <link href="externals/core/theme/navbar-fixed-top.css" rel="stylesheet">
    <?php
}
?>
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/core.css' />
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/space.css' />

<?php endblock(); ?>
<!-- body -->     
<?php startblock('content') ?>

<div class="col-xs-12 pm-tile-container"  >

            
    
    <div class="container">

        <div class="jumbotron" style="margin-top: 100px;">
            <h3>Home</h3>
        </div>
        
    </div>

</div> <!-- /container -->
<?php
endblock();

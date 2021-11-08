<?php include 'Modules/core/View/layout.php' ?>

<?php startblock('stylesheet') ?>

<link rel="stylesheet" type="text/css" href="externals/bootstrap/css/bootstrap.min.css">
<?php
$headless = Configuration::get("headless");
if (!$headless) {
    ?>
    <link href="data/core/theme/navbar-fixed-top.css" rel="stylesheet">
    <?php
}
?>
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/core.css' />
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/space.css' />

<style>
    .modulebox{
        border: solid 1px #e1e1e1; 
        border-bottom: solid 3px #e1e1e1; 
        height:325px; 
        width:220px; 
        margin-left: 25px;
        margin-top: 25px;
    }    
</style>

<?php endblock(); ?>
<!-- body -->     
<?php startblock('content') ?>


<div class="col-xs-12 pm-tile-container"  >
    <div class="container" style="margin-top: 50px;">

        <?php echo $content; ?>
        <div class="bs-glyphicons">
            <ul class="bs-glyphicons-list">
                
            </ul>
        </div>
    </div>
</div> <!-- /container -->
<?php
endblock();

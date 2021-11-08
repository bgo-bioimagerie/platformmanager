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
        <?php foreach($spaces as $item) { ?>

            <div class="col-xs-12 col-md-4 col-lg-2 modulebox">
                            <!-- IMAGE -->
                            <a href="<?php echo "corespace/" . $item["id"] ?>">
                            <?php if(isset($icon)) {?><img src="<?php echo $item["image"] ?>" alt="logo" style="margin-left: -15px;width:218px;height:150px"><?php } ?>
                            </a>
                            <p>
                            </p>
                            <!-- TITLE -->
                            <p style="color:#018181; ">
                                <a href="<?php echo "corespace/" . $item["id"] ?>"> <?php echo $item["name"] ?></a>
                                <?php if(isset($_SESSION["login"])) { ?>
                                        <a href="<?php echo "coretiles/1/0/unstar/".$item["id"] ?>"><span class="glyphicon glyphicon-star"></span></a>
                                <?php } ?>
                            </p>

                            <!-- DESC -->
                            <p style="color:#a1a1a1; font-size:12px;">
                                <?php echo $item["description"] ?>
                            </p>
                            <div style="position: absolute; bottom: 0px"><small>
                            <?php if($item["support"]) {  echo 'support: <a href="mailto:'.$item["support"].'">'.$item["support"].'</a>'; } ?>
                            </small></div>
                        </div>  



        <?php } ?>
    </div>
</div> <!-- /container -->
<?php
endblock();

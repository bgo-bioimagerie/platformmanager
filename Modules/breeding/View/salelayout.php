<!doctype html>
<?php require_once 'Modules/layout.php' ?>

<!-- header -->
<?php startblock('title') ?>
Platform-Manager
<?php endblock() ?>



<?php startblock('stylesheet') ?>
<link rel="stylesheet" href="externals/bootstrap/css/bootstrap.min.css">
<?php
$headless = Configuration::get("headless");
if (!$headless) {
    ?>
    <link href="data/core/theme/navbar-fixed-top.css" rel="stylesheet">
    <?php
}
?>
<link rel="stylesheet" href="Modules/core/Theme/core.css">
<link href="externals/datepicker/css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/spacemenu.css' />
<link rel='stylesheet' type='text/css' href='Modules/core/Theme/space.css' />
<?php endblock() ?> 



<?php
startblock('navbar');
require_once 'Modules/core/Controller/CorenavbarController.php';
$navController = new CorenavbarController(new Request(array(), false));
echo $navController->navbar();
endblock();
?>


<?php startblock('spacenavbar'); ?>
<div class="col-md-2 pm-space-navbar">
    <?php
    require_once 'Modules/core/Controller/CorespaceController.php';
    $spaceController = new CorespaceController(new Request(array(), false));
    echo $spaceController->navbar($id_space);
    
    $modelCoreConfig = new CoreConfig();
    $showNavBarBreeding = $modelCoreConfig->getParam("showNavBarBreeding", $id_space);
    ?>
</div> 
<?php if ($showNavBarBreeding) { ?>
<div class="col-md-8">
    <?php } else { ?>
        <div class="col-md-10">
        <?php } ?>

    <div class="col-md-12 pm-table-short">
        <div class="col-md-7">
            <h3><?php echo BreedingTranslator::Sale($lang) ?></h3>
        </div>
        <div class="text-center">
            <div class="btn-group btn-group-sm">
                <a class="btn btn-default <?php
                if ($activTab == "tracking") {
                    echo "active";
                }
                ?>" href="<?php echo "brsaleedit/" . $id_space . "/" . $id_sale ?>" style="height: 50px;">
                    <span class="glyphicon glyphicon-cog" style="font-size: 20px"></span>
                    <p><?php echo BreedingTranslator::Tracking($lang) ?></p>
                </a>
                <a class="btn btn-default <?php
                   if ($activTab == "detail") {
                       echo "active";
                   }
                ?>" href="<?php echo "brsaleitems/" . $id_space . "/" . $id_sale ?>" style="height: 50px;">
                    <span class="glyphicon glyphicon-plus" style="font-size: 20px"></span>
                    <p><?php echo BreedingTranslator::Details($lang) ?></p>
                </a>
                <a class="btn btn-default <?php
                   if ($activTab == "deliveryform") {
                       echo "active";
                   }
                ?>" href="<?php echo "brsaledeliveryform/" . $id_space . "/" . $id_sale ?>" style="height: 50px;">
                    <span class="glyphicon glyphicon-plane" style="font-size: 20px"></span>
                    <p><?php echo BreedingTranslator::DeliveryForm($lang) ?></p>
                </a>
                <a class="btn btn-default <?php
                   if ($activTab == "invoicing") {
                       echo "active";
                   }
                ?>" href="<?php echo "brsaleinvoice/" . $id_space . "/" . $id_sale ?>" style="height: 50px;">
                    <span class="glyphicon glyphicon-certificate" style="font-size: 20px"></span>
                    <p><?php echo BreedingTranslator::Invoicing($lang) ?></p>
                </a>
                
            </div>
        </div>    
    </div>


    <div class="col-md-12 pm-table">
<?php endblock(); ?>




        <?php startblock('content') ?>
<?php endblock() ?>    






    <?php startblock('footer') ?>
    </div>
</div>

<?php if ($showNavBarBreeding) { ?>
<div class="col-md-2 pm-space-navbar-right" >
<?php
require_once 'Modules/breeding/Controller/BreedingController.php';
$menucontroller = new BreedingController(new Request(array(), false));
echo $menucontroller->navbar($id_space);
?>
<?php } ?>
</div>
</div>
<?php
endblock();

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
    <link href="externals/core/theme/navbar-fixed-top.css" rel="stylesheet">
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
<div class="col-md-12 pm-space-navbar">
    <?php
    require_once 'Modules/core/Controller/CorespaceController.php';
    $spaceController = new CorespaceController(new Request(array(), false));
    echo $spaceController->navbar($id_space);

    $modelCoreConfig = new CoreConfig();
    $showNavBarBreeding = $modelCoreConfig->getParam("showNavBarBreeding", $id_space);
    ?>
</div> 

        <div class="col-md-2 pm-space-navbar-right" >
            <?php
            require_once 'Modules/breeding/Controller/BreedingController.php';
            $menucontroller = new BreedingController(new Request(array(), false));
            echo $menucontroller->navbar($id_space);
            ?>
        </div>

        <div class="col-md-10">

        <div class="col-md-12 pm-table-short">
            <div class="col-md-6">
                <h3><?php echo BreedingTranslator::Batch($lang) . $batch["reference"] ?></h3>

                <table class="table">
                    <tr>
                        <td><?php echo BreedingTranslator::Quantity($lang) ?></td>
                        <td><?php echo $batch["quantity"] ?></td>
                    </tr>
                    <tr>
                        <td><?php echo BreedingTranslator::InitialQuantity($lang) ?></td>
                        <td><?php echo $batch["quantity_start"] ?> </td>
                    </tr>
                    <tr>
                        <td><?php echo BreedingTranslator::Losses($lang) ?></td>
                        <td><?php echo $batch["quantity_losse"] ?></td>
                    </tr>
                    <tr>
                        <td><?php echo BreedingTranslator::Sales($lang) ?></td>
                        <td><?php echo $batch["quantity_sale"] ?></td>
                    </tr>


                    <?php
                    if ($batch["sexing_date"] != "0000-00-00") {
                        ?>
                        <tr>
                            <td><?php echo BreedingTranslator::SexingDate($lang) . ": " . CoreTranslator::dateFromEn($batch["sexing_date"], $lang) ?></td>
                            <td>
                                <a href="brbatch/<?php echo $id_space ?>/<?php echo $batch["sexing_f_batch_id"] ?>"><?php echo $batch["sexing_female_num"] . " " . BreedingTranslator::Females($lang) ?></a>
                                <a href="brbatch/<?php echo $id_space ?>/<?php echo $batch["sexing_m_batch_id"] ?>"><?php echo $batch["sexing_male_num"] . " " . BreedingTranslator::Males($lang) ?></a>

                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </table>
            </div>
            <div class="text-center">
                <div class="btn-group btn-group-sm">

                    <a class="btn btn-default <?php
                    if ($activTab == "infos") {
                        echo "active";
                    }
                    ?>" href="<?php echo "brbatch/" . $id_space . "/" . $batch["id"] ?>" style="height: 50px;">
                        <span class="glyphicon glyphicon-file" style="font-size: 20px"></span>
                        <p><?php echo BreedingTranslator::Infos($lang) ?></p>
                    </a>
                    <a class="btn btn-default <?php
                    if ($activTab == "moves") {
                        echo "active";
                    }
                    ?>" href="<?php echo "brmoves/" . $id_space . "/" . $batch["id"] ?>" style="height: 50px;">
                        <span class="glyphicon glyphicon-resize-vertical" style="font-size: 20px"></span>
                        <p><?php echo BreedingTranslator::Moves($lang) ?></p>
                    </a>
                    <a class="btn btn-default <?php
                    if ($activTab == "treatments") {
                        echo "active";
                    }
                    ?>" href="<?php echo "brtreatments/" . $id_space . "/" . $batch["id"] ?>" style="height: 50px;">
                        <span class="glyphicon glyphicon-plus" style="font-size: 20px"></span>
                        <p><?php echo BreedingTranslator::Treatments($lang) ?></p>
                    </a>
                    <a class="btn btn-default <?php
                    if ($activTab == "chipping") {
                        echo "active";
                    }
                    ?>" href="<?php echo "brchipping/" . $id_space . "/" . $batch["id"] ?>" style="height: 50px;">
                        <span class="glyphicon glyphicon-certificate" style="font-size: 20px"></span>
                        <p><?php echo BreedingTranslator::Chipping($lang) ?></p>
                    </a>
                    <a class="btn btn-default <?php
                    if ($activTab == "sexing") {
                        echo "active";
                    }
                    ?>" href="<?php echo "brsexing/" . $id_space . "/" . $batch["id"] ?>" style="height: 50px;">
                        <span class="glyphicon glyphicon-tags" style="font-size: 20px"></span>
                        <p><?php echo BreedingTranslator::Sexing($lang) ?></p>
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

</div>
<?php
endblock();

<?php include 'Modules/core/View/spacelayout.php' ?>

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


<?php endblock(); ?>

<?php startblock('spacenavbar'); ?>

<?php endblock(); ?>

<!-- body -->     
<?php startblock('content') ?>

<?php
if ($space['color'] == "") {
    $space['color'] = "#428bca";
}
?>

<div class="col-xs-12 text-center" style="color: #fff; background-color: <?php echo $space['color'] ?>; height: 35px;">
    <h4><?php echo $space['name'] ?></h4>
</div>

<div>
    <!-- display com popup -->
<?php
    if ($showCom) {
?>

<!--  *************  -->
<!--  Popup windows  -->
<!--  *************  -->
<link rel="stylesheet" type="text/css" href="Framework/pm_popup.css">
<div id="hider" class="col-xs-12"></div> 
<div id="entriespopup_box" class="pm_popup_box" style="display: none;">
    <div class="col-md-1 col-md-offset-11" style="text-align: right;">
        <a id="entriesbuttonclose" class="glyphicon glyphicon-remove" style="cursor:pointer;"></a>
    </div>
    <div class="row">
        <div id="content_section" class="col-md-12" style="text-align:center">
        </div>
    </div>
</div>
<?php include 'Modules/com/View/Comhome/comhomeScript.php';  ?>

<?php
    }
?>
</div>


<div class="col-xs-12" style="background-color: #fff; height: 2000px;">

    <div class="container" style="background-color: #fff;">

        <?php
        require_once 'Modules/com/Controller/ComtileController.php';
        $navController = new ComtileController(new Request(array(), false));
        echo $navController->indexAction($id_space);
        ?>

        <div class="page-header">
            <h2>
                <?php echo CoreTranslator::Tools($lang) ?>
                <br>
            </h2>
        </div>
        <div class="pm-tiles" >
            <div class="pm-tiles bs-glyphicons">
                <ul class="pm-tiles bs-glyphicons-list">
                    <?php
                    $configModel = new CoreConfig();
                    foreach ($spaceMenuItems as $item) {
                        ?>
                        <li style="background-color:<?php echo $item["color"] ?>;">
                            <a href="<?php echo $item["url"] . "/" . $id_space ?>">
                                <span class="pm-tiles glyphicon <?php echo $item["icon"] ?>" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class"><?php echo $item["name"] ?></span>
                            </a>
                        </li>
                        <?php
                    }
                    ?>
                    <ul/>
            </div>
        </div>
        <?php
        if ($showAdmMenu) {
            ?>
            <div class="page-header">
                <h2>
                    <?php echo CoreTranslator::Admin($lang) ?>
                    <br>
                </h2>
            </div>
            <div class="pm-tiles" >
                <div class="pm-tiles bs-glyphicons">
                    <ul class="pm-tiles bs-glyphicons-list">
                        <li style="background-color:<?php echo $space['color'] ?>;">
                            <a href="<?php echo "spaceadminedit/" . $space["id"] ?>">
                                <span class="pm-tiles glyphicon glyphicon-cog" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class"><?php echo CoreTranslator::Space($lang) ?></span>
                            </a>
                        </li>
                        <li style="background-color:<?php echo $space['color'] ?>;">
                            <a href="<?php echo "spaceconfig/" . $space["id"] ?>">
                                <span class="pm-tiles glyphicon glyphicon-cog" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class"><?php echo CoreTranslator::Configuration($lang) ?></span>
                            </a>
                        </li>
                        <li style="background-color:<?php echo $space['color'] ?>;">
                            <a href="<?php echo "corespaceaccess/" . $space["id"] ?>">
                                <span class="pm-tiles glyphicon glyphicon-user" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class"><?php echo CoreTranslator::Users($lang) ?></span>
                            </a>
                        </li> 

                        <li style="background-color:<?php echo $space['color'] ?>;">
                            <a href="<?php echo "corespacehistory/" . $space["id"] ?>">
                                <span class="pm-tiles glyphicon glyphicon-th-list" aria-hidden="true"></span>
                                <span class="pm-tiles glyphicon-class"><?php echo CoreTranslator::History($lang) ?></span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <?php
        }
        ?>
        <?php if($role<CoreSpace::$MANAGER) { ?>
        <div class="page-header">
                <h2>
                    <?php echo CoreTranslator::RequestJoin(true, $lang)."?" ?>
                    <br>
                </h2>
        </div>
        <div>
            <a href="<?php echo "coretilesselfjoinspace/". $space["id"] ?>">
                <button type="button" class="btn btn-md btn-danger">
                    <?php echo CoreTranslator::RequestJoin(true, $lang) ?>
                </button>
            </a>
        </div>
        <?php } ?>


    </div> <!-- /container -->
</div>
<?php
endblock();

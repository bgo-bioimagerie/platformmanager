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
<link rel='stylesheet' type='text/css' href='Modules/estore/Theme/sale.css' />

<title>jQuery mycart Plugin Example</title>
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<style>
    body { font-family:'Open Sans'}
    .badge-notify{
        background:red;
        position:relative;
        top: -20px;
        right: 10px;
    }
</style>

<?php endblock() ?> 



<?php
startblock('navbar');
require_once 'Modules/core/Controller/CorenavbarController.php';
$navController = new CorenavbarController(new Request(array(), false));
echo $navController->navbar();
?>
<div class="col-md-12" style="margin-top: 7px; margin-bottom: -14px;">
    <?php
    if (isset($_SESSION["message"])) {
        ?>
        <div class="alert alert-info">
            <?php echo $_SESSION["message"] ?>
        </div>
        <?php
        unset($_SESSION["message"]);
    }
    ?>
</div>
<?php
$id_user = $_SESSION["id_user"];
$modelSpace = new CoreSpace();
$role = $modelSpace->getUserSpaceRole($id_space, $id_user);

endblock();
?>


<?php startblock('spacenavbar'); ?>
<div class="col-md-12 col-lg-12">
<?php
if ($role > 2) {
    ?>
    <div class="col-md-2 col-lg-3" style="padding-left: 25px;">
        <?php
        require_once 'Modules/estore/Controller/EstoreController.php';
        $menucontroller = new EstoreController(new Request(array(), false));
        echo $menucontroller->navbar($id_space);
        ?>
    </div>
    <?php
}
?>
<div class="col-md-10 col-lg-9">
    <?php endblock(); ?>

    <?php startblock('content') ?>
    <?php endblock() ?>    

    <?php startblock('footer') ?>

</div>
</div>

<?php
endblock();

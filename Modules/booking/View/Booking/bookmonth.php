<?php include 'Modules/booking/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<?php 
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/View/Booking/agendafunction.php';
?>


<link href="Modules/booking/Theme/styleagenda.css" rel="stylesheet" type="text/css" />


<?php
    if (empty($resourceInfo)) {
?>
    <div class="col-sm-12" style="background-color: #ffffff; padding-top: 12px;">
    <div class="col-sm-10 col-sm-offset-1">
    <?php
            $message = "";
                if (isset($_SESSION["message"]) && $_SESSION["message"]){
                $message = $_SESSION["message"];
            } ?>
            <?php if ($message != ""): 
            if (strpos($message, "Err") === false){?>
                <div class="alert alert-success text-center">	
            <?php 
            }
            else{
            ?>
                <div class="alert alert-danger text-center">
            <?php 
            }
        ?>
            <p><?php echo  $message ?></p>
            </div>
        <?php endif; unset($_SESSION["message"])?>

    </div>
    </div>
<?php
    }
?>

<div class="col-xs-12" style="background-color: #ffffff;">
<?php
drawAgenda($id_space, $lang, $month, $year, $calEntries, $resourcesBase, $agendaStyle, $resourceInfo);
?>
</div>

<div class="col-xs-12" style="background-color: #ffffff;">
<?php include "Modules/booking/View/colorcodenavbar.php"; ?>
</div>

<?php endblock();

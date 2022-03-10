<?php include 'Modules/booking/View/layout.php' ?>

    
<?php startblock('content') ?>


<?php 
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/View/Booking/bookfunction.php';
require_once 'Modules/booking/View/Booking/agendafunction.php';


$startDate = $date;
$toDate = null;
$nbDays = 1;
$resourcesBase = $resourceBase ? [$resourceBase] : [];
$calEntries = [$calEntries];
$from = ["day", $date, $bk_id_resource, $bk_id_area, $id_user, $detailedView ? 'detailed' : 'simple'];
$isUserAuthorizedToBook = [ $isUserAuthorizedToBook];

echo drawNavigation('day', $id_space, $startDate, $toDate, $beforeDate, $afterDate, $bk_id_resource, $bk_id_area, $id_user, $detailedView, $lang);
?>
<div class="container">
<?php
if($detailedView) {
    include 'Modules/booking/View/Booking/caldisplay.php';
} else {
    include 'Modules/booking/View/Booking/simplecaldisplay.php';
}
?>
</div>

<div class="row" style="background-color: #ffffff;">
	<div class="col-sm-12">
		<?php include "Modules/booking/View/colorcodenavbar.php"; ?>
	</div>
</div>


<?php endblock(); ?>

<?php include 'Modules/booking/View/layout.php' ?>

    
<?php startblock('content') ?>


<?php 
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/View/Booking/bookfunction.php';
require_once 'Modules/booking/View/Booking/agendafunction.php';


$startDate = $date;
$toDate = null;
$nbDays = 1;
$resourcesBase = [$resourceBase];
$calEntries = [$calEntries];
$from = ["day", $date, $bk_id_resource, $bk_id_area, $id_user];

echo drawNavigation('day', $id_space, $startDate, $toDate, $beforeDate, $afterDate, $bk_id_resource, $bk_id_area, $id_user, $lang);
?>
<div class="container">
<?php
include 'Modules/booking/View/Booking/caldisplay.php';
?>
</div>

<div class="row" style="background-color: #ffffff;">
	<div class="col-12">
		<?php include "Modules/booking/View/colorcodenavbar.php"; ?>
	</div>
</div>


<?php endblock(); ?>

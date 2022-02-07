<?php include 'Modules/booking/View/layout.php' ?>

    
<?php startblock('content') ?>

<?php 
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/View/Booking/bookfunction.php';
require_once 'Modules/booking/View/Booking/agendafunction.php';

$startDate = $mondayDate;
$toDate = $sundayDate;
$nbDays = 7;
$resourcesBase = [$resourceBase];
$calEntries = [$calEntries];


echo drawNavigation('week', $id_space, $startDate, $toDate, $beforeDate, $afterDate, $bk_id_resource, $bk_id_area, $id_user, $lang);
include 'Modules/booking/View/Booking/caldisplay.php';
		
?>



<div class="row">
	<div class="col-sm-12">
	<?php include "Modules/booking/View/colorcodenavbar.php"; ?>
	</div>
</div>


<?php endblock(); ?>

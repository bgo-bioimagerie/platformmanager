<?php include 'Modules/booking/View/layout.php' ?>

    
<?php startblock('content') ?>

<?php 
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/View/Booking/bookfunction.php';
require_once 'Modules/booking/View/Booking/agendafunction.php';

$startDate = $date;
$toDate = null;
$nbDays = 1;
$from = ["dayarea", $date, $bk_id_resource, $bk_id_area, $id_user, $detailedView ? 'detailed' : 'simple'];

if($bk_id_area == null) { $bk_id_area = '';}
if($bk_id_resource == null) { $bk_id_resource = '';}

echo drawNavigation('dayarea', $id_space, $startDate, $toDate, $beforeDate, $afterDate, $bk_id_resource, $bk_id_area, $id_user, $detailedView, $lang);
if($detailedView) {
    include 'Modules/booking/View/Booking/caldisplay.php';
} else {
    include 'Modules/booking/View/Booking/simplecaldisplay.php';
}


?>

<div class="row">
	<div class="col-12">
	<?php include "Modules/booking/View/colorcodenavbar.php"; ?>
	</div>
</div>

<?php endblock(); ?>


<?php include_once 'Modules/booking/View/layout.php' ?>

<?php startblock('meta') ?>
    <meta name="robots" content="noindex" />
<?php endblock() ?>
    
<?php startblock('content') ?>

<?php
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/View/Booking/bookfunction.php';
require_once 'Modules/booking/View/Booking/agendafunction.php';

$startDate = $mondayDate;
$toDate = $sundayDate;
$nbDays = 7;
$resourcesBase = $resourceBase ? [$resourceBase] : [];
$calEntries = [$calEntries];
$from = ["week", $date, $bk_id_resource, $bk_id_area, $id_user, $detailedView ? 'detailed' : 'simple'];
$isUserAuthorizedToBook = [ $isUserAuthorizedToBook];
if ($bk_id_area == null) {
    $bk_id_area = '';
}
if ($bk_id_resource == null) {
    $bk_id_resource = '';
}

echo drawNavigation('week', $id_space, $startDate, $toDate, $beforeDate, $afterDate, $bk_id_resource, $bk_id_area, $id_user, $detailedView, $lang);
if ($detailedView) {
    include_once 'Modules/booking/View/Booking/caldisplay.php';
} else {
    include_once 'Modules/booking/View/Booking/simplecaldisplay.php';
}

?>

<div class="row">
    <div class="col-12">
    <?php include_once "Modules/booking/View/colorcodenavbar.php"; ?>
    </div>
</div>


<?php endblock(); ?>

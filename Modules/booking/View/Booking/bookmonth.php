<?php include_once 'Modules/booking/View/layout.php' ?>

<?php startblock('meta') ?>
	<meta name="robots" content="noindex" />
<?php endblock() ?>
    
<?php startblock('content') ?>

<?php
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/View/Booking/agendafunction.php';
?>


<link href="Modules/booking/Theme/styleagenda.css" rel="stylesheet" type="text/css" />

<div class="row" style="background-color: #ffffff;">
    <div class="col-12">
    <?php

    $from = ["month", $date, $bk_id_resource, $bk_id_area, $idUser, $detailedView ? 'detailed' : 'simple'];
if ($bk_id_area == null) {
    $bk_id_area = '';
}
if ($bk_id_resource == null) {
    $bk_id_resource = '';
}

$nav = [
    'date' => $date,
    'beforeDate' => $beforeDate,
    'afterDate' => $afterDate,
    'bk_id_area' => $bk_id_area,
    'bk_id_resource' => $bk_id_resource,
    'id_user' => $idUser
];
echo drawNavigation('month', $idSpace, $date, null, $beforeDate, $afterDate, $bk_id_resource, $bk_id_area, $idUser, $detailedView, $lang);

drawAgenda($idSpace, $lang, $month, $year, $calEntries, $resourcesBase, $agendaStyle, $resourceInfo, $nav, $from, $context['role']);
?>
    </div>
</div>

<div class="row">
    <div class="col-12" style="background-color: #ffffff;">
    <?php include_once "Modules/booking/View/colorcodenavbar.php"; ?>
    </div>
</div>

<?php endblock(); ?>

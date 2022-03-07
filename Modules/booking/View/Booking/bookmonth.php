<?php include 'Modules/booking/View/layout.php' ?>

    
<?php startblock('content') ?>

<?php 
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/View/Booking/agendafunction.php';
?>


<link href="Modules/booking/Theme/styleagenda.css" rel="stylesheet" type="text/css" />

<div class="row" style="background-color: #ffffff;">
    <div class="col-12">
    <?php

    $from = ["month", $date, $bk_id_resource, $bk_id_area, $id_user, $detailedView ? 'detailed' : 'simple'];

    $nav = [
        'date' => $date,
        'beforeDate' => $beforeDate,
        'afterDate' => $afterDate,
        'bk_id_area' => $bk_id_area,
        'bk_id_resource' => $bk_id_resource,
        'id_user' => $id_user
    ];
    echo drawNavigation('month', $id_space, $date, null, $beforeDate, $afterDate, $bk_id_resource, $bk_id_area, $id_user, $detailedView, $lang);

    drawAgenda($id_space, $lang, $month, $year, $calEntries, $resourcesBase, $agendaStyle, $resourceInfo, $nav, $from, $context['role']);
    ?>
    </div>
</div>

<div class="row">
    <div class="col-12" style="background-color: #ffffff;">
    <?php include "Modules/booking/View/colorcodenavbar.php"; ?>
    </div>
</div>

<?php endblock(); ?>

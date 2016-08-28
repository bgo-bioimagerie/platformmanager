<?php include 'Modules/booking/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<?php 
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/View/Booking/agendafunction.php';
?>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<link href="Modules/booking/Theme/styleagenda.css" rel="stylesheet" type="text/css" />
</head>
	
<div class="col-xs-12" style="background-color: #ffffff;">
<?php 	
drawAgenda($id_space, $lang, $month, $year, $calEntries, $resourceBase);
?>
</div>

<div class="col-xs-12" style="background-color: #ffffff;">
<?php include "Modules/booking/View/colorcodenavbar.php"; ?>
</div>

<?php endblock();

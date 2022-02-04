<?php include 'Modules/booking/View/layout.php' ?>

    
<?php startblock('content') ?>

<?php 
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/View/Booking/bookfunction.php';
require_once 'Modules/booking/View/Booking/agendafunction.php';
?>	
	
 
<style>

a{
	width: 100%;
	color: <?php echo  $agendaStyle["header_background"] ?>;
}

#tcell{
	border-left: 1px solid #d1d1d1;
	border-right: 1px solid #d1d1d1;
	border-bottom: 1px solid #d1d1d1;
}

#tcelltop{
	border: 1px solid #d1d1d1;
	position: relative;
	overflow: hidden;
}

#colDiv{
	padding:0px;
    margin:0px;
    position: relative;
}

#tcellResa{
	-moz-border-radius: 0px;
	border-radius: 0px;
	border: 1px solid #d1d1d1;
	overflow: hidden;
}

#resa_link{
	font-family: Arial;
	font-size: 12px;
	line-height: 12px;
	letter-spacing: 1px;
	font-weight: normal;
	color: #000;
}

</style>
<!-- Add the table title -->
<div class="row" style="background-color: #ffffff; padding-bottom: 12px;">

	<div class="col-md-6 text-left">
	    <div class="btn-group" role="group" aria-label="navigate by day">

<?php
	$today = date("Y-m-d", time());
	$qc = '?'.implode('&', ["bk_curentDate=$date", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
	$qt = '?'.implode('&', ["bk_curentDate=$today", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
	$qb = '?'.implode('&', ["bk_curentDate=$beforeDate", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
	$qa = '?'.implode('&', ["bk_curentDate=$afterDate", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
?>
			<a aria-label="day before" href="bookingday/<?php echo "$id_space/$qb" ?>"><button type="button" class="btn btn-default"> <span class="glyphicon glyphicon-menu-left"></span> </button></a>
			<a aria-label="day after" href="bookingday/<?php echo "$id_space/$qa" ?>"><button type="button" class="btn btn-default"> <span class="glyphicon glyphicon-menu-right"></span> </button></a>
			<a aria-label="current day" href="bookingday/<?php echo "$id_space/$qt" ?>"><button type="button" class="btn btn-default"> <?php echo  BookingTranslator::Today($lang) ?> </button></a>
		</div>
		<?php 
	$d = explode("-", $date);
	$time = mktime(0,0,0,$d[1],$d[2],$d[0]);
	$dayStream = date("l", $time);
	$monthStream = date("F", $time);
	$dayNumStream = date("d", $time);
	$yearStream = date("Y", $time);
	$sufixStream = date("S", $time);
	$day_position = date("w", $time); // 0 for sunday, 6 for saturday
	$day_position = ($day_position === "0") ? 7 : intval($day_position);
	?>
		<strong><?php echo  BookingTranslator::DateFromTime($time, $lang) ?></strong>
	</div>

	<div class="col-md-6 text-right">
		<div class="btn-group" role="group" aria-label="...">
			<div class="btn btn-default active" type="button">
			<a style="color:#333;" href="bookingday/<?php echo $id_space.$qc ?>" ><?php echo  BookingTranslator::Day($lang) ?></a>
			</div>
			<div class="btn btn-default" type="button">
				<a style="color:#333;" href="bookingdayarea/<?php echo $id_space.$qc ?>" ><?php echo  BookingTranslator::Day_Area($lang) ?></a>
			</div>
			<div class="btn btn-default" type="button">
				<a style="color:#333;" href="bookingweek/<?php echo $id_space.$qc ?>" ><?php echo  BookingTranslator::Week($lang) ?></a>
			</div>
			<div class="btn btn-default" type="button">
				<a style="color:#333;" href="bookingweekarea/<?php echo $id_space.$qc ?>" ><?php echo  BookingTranslator::Week_Area($lang) ?></a>
			</div>
			<div class="btn btn-default" type="button">
				<a style="color:#333;" href="bookingmonth/<?php echo $id_space.$qc ?>" ><?php echo  BookingTranslator::Month($lang) ?></a>
			</div>
			
		</div>
	</div>
</div>

<?php 
$day_begin = $this->clean($scheduling['day_begin']);
$day_end = $this->clean($scheduling['day_end']);
$size_bloc_resa = $this->clean($scheduling['size_bloc_resa']);
$isAvailableDay = false;
$available_days = $scheduling["is_monday"] . "," . $scheduling["is_tuesday"]. "," . $scheduling["is_wednesday"]. "," . $scheduling["is_thursday"]. "," . $scheduling["is_friday"]. "," . $scheduling["is_saturday"]. "," . $scheduling["is_sunday"];
$available_days = explode(",", $available_days);
if ($available_days[$day_position-1] == 1){
	$isAvailableDay = true;
}
?>

<?php

$colHeader = compute($id_space, $size_bloc_resa, $date_unix, $day_begin, $day_end, $calEntries, $isUserAuthorizedToBook, $isAvailableDay, $agendaStyle, $bk_id_resource);
ksort($colHeader);
?>

<div class="table-responsive">
<table aria-label="bookings day view" class="table">
<thead>
	<tr>
		<th scope="col"></th>
		<th colspan="1" scope="col" id="resource" style="text-align: center">
		<?php
		echo $resourceBase['name'];
		if($resourceBase['last_state'] != ""){
			echo '<br/><a class="btn btn-xs" href="resourcesevents/'.$id_space.'/'.$resourceBase['id'].'" style="background-color:'.$resourceBase['last_state'].' ; color: #fff; width:12px; height: 12px;"></a>';
		}
		?>
		</th>
	</tr>
	<tr>
		<th id="time">Time</th>
		<th id="bookings">Bookings</th>
	</tr>
</thead>
<tbody>
	<?php
	foreach ($colHeader as $i => $hCal) {
		$hCalEntries = $hCal['entries'];
		$hPlus = $hCal['plus'];
		$newHour = true;
	?>
		<tr>
			<td headers="time" class="col-xs-2"><?php echo $i ?>:00</td>
			<td headers="resource bookings">
				<?php if ($hPlus) { ?>
				<div><a class="glyphicon glyphicon-plus" href="<?php echo $hPlus ?>"></a></div>
				<?php } ?>
				<?php foreach($hCalEntries as $c => $hcalEntry) {?>
					<div class="text-center tcellResa"  style="background-color:<?php echo $hcalEntry['color_bg']?>; ">
						<a class="text-center" style="color:<?php echo $hcalEntry['color_text']?>; font-size: <?php echo $agendaStyle["resa_font_size"] ?>px;" href="<?php echo $hcalEntry['link'] ?>"><?php echo $hcalEntry['text']; ?>
						<?php if($hcalEntry['expand']) {
							echo '<div>'.$hcalEntry['hstart'].' - '.$hcalEntry['hend'].'</div>';
							$newHour = false;
						}?>
						</a>
					</div>
				<?php } ?>
			</td>
		<tr>
	<?php }	?>
</tbody>
</table>
</div>

<div class="row" style="background-color: #ffffff;">
	<div class="col-sm-12">
		<?php include "Modules/booking/View/colorcodenavbar.php"; ?>
	</div>
</div>

<?php endblock(); ?>

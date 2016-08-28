<?php include 'Modules/booking/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<?php 
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/View/Booking/bookfunction.php';
?>

<?php 
$dayWidth = 100/count($resourcesBase);
?>

<head>
 
<style>

a{
	width: 100%;
	color: <?php echo $agendaStyle["header_background"] ?>;
}

#tcell{
	border-left: 1px solid #d1d1d1;
	border-right: 1px solid #d1d1d1;
	border-bottom: 1px solid #d1d1d1;
}

#tcelltop{
	border: 1px solid #d1d1d1;
}

#colDiv{
	padding:0px;
    margin:0px;
    position:relative;
}

#colDivleft{
	padding-right:0px;
	margin-right:0px;
	position:relative;
}

#colDivright{
	padding-left:0px;
	margin-left:0px;
	position:relative;
}


#tcellResa{
	-moz-border-radius: 0px;
	border-radius: 0px;
	border: 1px solid #f1f1f1;
}

#resa_link{
	font-family: Arial;
	font-size: 12px;
	line-height: 12px;
	letter-spacing: 1px;
	font-weight: normal;
	color: #000;
}

@media (min-width: 1200px) {
  .seven-cols .col-md-1,
  .seven-cols .col-sm-1,
  .seven-cols .col-lg-1 {
    width: <?php echo $dayWidth?>%;
    *width: <?php echo $dayWidth?>%;
  }
}
/* 14% = 100% (full-width row) divided by 7 */

img{
  max-width: 100%;
}

</style>
</head>


<!-- Add the table title -->
<div class="col-md-12"  style="background-color: #ffffff; padding-top: 12px;">
<div class="col-md-10">
<?php
        $message = "";
            if (isset($_SESSION["message"])){
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

<div class="col-lg-12" style="background-color: #ffffff; padding-bottom: 12px;">

<div class="col-md-6 text-left">
    <div class="btn-group" role="group" aria-label="...">
<button type="submit" class="btn btn-default" onclick="location.href='bookingdayarea/<?php echo $id_space ?>/daybefore'"> &lt; </button>
<button type="submit" class="btn btn-default" onclick="location.href='bookingdayarea/<?php echo $id_space ?>/dayafter'"> > </button>
<button type="submit" class="btn btn-default" onclick="location.href='bookingdayarea/<?php echo $id_space ?>/today'"><?php echo  BookingTranslator::Today($lang) ?></button>
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
for ($p = 0 ; $p < count($day_position) ; $p++){
	if ($day_position[$p] == 0){
		$day_position[$p] = 7;
	}
}
?>
<b><?php echo  BookingTranslator::DateFromTime($time, $lang) ?></b>
</div>


<div class="col-md-6 text-right">
    <div class="btn-group" role="group" aria-label="...">
        <button type="button" onclick="location.href='bookingday/<?php echo $id_space ?>'" class="btn btn-default"><?php echo  BookingTranslator::Day($lang) ?></button>
        <button type="button" class="btn btn-default active"><?php echo  BookingTranslator::Day_Area($lang) ?></button>
        <button type="button" onclick="location.href='bookingweek/<?php echo $id_space ?>'" class="btn btn-default "><?php echo  BookingTranslator::Week($lang) ?></button>
        <button type="button" onclick="location.href='bookingweekarea/<?php echo $id_space ?>'" class="btn btn-default "><?php echo  BookingTranslator::Week_Area($lang) ?></button>
        <button type="button" onclick="location.href='bookingmonth/<?php echo $id_space ?>'" class="btn btn-default"><?php echo  BookingTranslator::Month($lang) ?></button>
    </div>
    </div>
</div>

<?php 
$day_begin = $this->clean($scheduling['day_begin']);
$day_end = $this->clean($scheduling['day_end']);
$size_bloc_resa = $this->clean($scheduling['size_bloc_resa']);
?>

<!-- hours column -->
<div class="col-xs-12">
<div class="col-xs-1" id="colDiv">

	<div id="tcelltop" style="height: <?php echo $agendaStyle["header_height"]?>px; background-color:<?php echo $agendaStyle["header_background"]?>;">

	</div>
	<?php 
	// Hours
	for ($h = $day_begin ; $h < $day_end ; $h++){
		$heightCol = "0px";
		if ($size_bloc_resa == 900){
			$heightCol = 4*$agendaStyle["line_height"] . "px";
		}
		else if($size_bloc_resa == 1800){
			$heightCol = 2*$agendaStyle["line_height"] . "px";;
		}
		else if($size_bloc_resa == 3600){
			$heightCol = $agendaStyle["line_height"] . "px";;
		}
		?>
	
		<div id="tcell" style="height: <?php echo  $heightCol ?>; background-color: <?php echo $agendaStyle["header_background"]?>; color: <?php echo $agendaStyle["header_color"]?>; font-size: <?php echo  $agendaStyle["header_font_size"]?>px">
		<?php echo $h?>:00
		</div>
	<?php 	
	}
	?>	
</div>	
	
<!-- hours reservation -->	
<div class="col-xs-11" id="colDiv">
	
	<div class="row seven-cols" id="colDiv">
	<?php 
	for($r = 0 ; $r < count($resourcesBase) ; $r++){
	?>
	
	<div class="col-lg-1 col-md-3 col-sm-4 col-xs-6" id="colDiv">

	<div id="tcelltop" style="height: <?php echo $agendaStyle["header_height"]?>px; background-color: <?php echo $agendaStyle["header_background"]?>; color: <?php echo $agendaStyle["header_color"]?>; font-size: <?php echo  $agendaStyle["header_font_size"]?>px">
	<p class="text-center"><b><?php echo  $this->clean($resourcesBase[$r]['name']) ?></b><br/><?php echo  $this->clean($resourcesBase[$r]['description']) ?></p>
	</div>

	<?php 
        $available_days = $scheduling["is_monday"] . "," . $scheduling["is_tuesday"]. "," . $scheduling["is_wednesday"]. "," . $scheduling["is_thursday"]. "," . $scheduling["is_friday"]. "," . $scheduling["is_saturday"]. "," . $scheduling["is_sunday"];
	//$available_days = $this->clean($scheduling['available_days']);
	$available_days = explode(",", $available_days);
	
	$curentDay = date("w", $date_unix);

	$curentDay--;
	if ($curentDay == -1){
		$curentDay = 6;
	}

	$isAvailableDay = false;
	if ($available_days[$curentDay] == 1){
		$isAvailableDay = true;
	}
	
	bookday($id_space, $size_bloc_resa, $date_unix, $day_begin, $day_end, $calEntries[$r], $isUserAuthorizedToBook[$r], $isAvailableDay, $agendaStyle, $resourcesBase[$r]["id"]);
	?>
	
	</div>
	<?php 
	}
	?>
</div>
</div>

<div class="col-xs-12">

<?php include "Modules/booking/View/colorcodenavbar.php"; ?>

</div>

<?php endblock();


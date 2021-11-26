<?php include 'Modules/booking/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<?php 
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/View/Booking/bookfunction.php';
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
}
#resa_link{
	color: <?php echo  "#" . $agendaStyle["resa_color"] ?>;
	font-size: <?php echo  $agendaStyle["resa_font_size"] ?>;
}

</style>

<!-- Add the table title -->
<div class="row"  style="background-color: #ffffff; padding-top: 12px;">
		<?php
			$message = "";
			if (isset($_SESSION["message"]) && $_SESSION["message"]){
				$message = $_SESSION["message"];
			}
			if ($message) {
		?>
		<?php
			if (strpos($message, "Err") === false){
		?>
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
		<?php } ?>
		<?php unset($_SESSION["message"])?>
</div>
<div class="row" style="background-color: #ffffff; padding-bottom: 12px;">

	<div class="col-md-6 text-left">
	    <div class="btn-group" role="group" aria-label="...">
			<button type="submit" class="btn btn-default" onclick="location.href='bookingday/<?php echo $id_space ?>/daybefore'"> &lt; </button>
			<button type="submit" class="btn btn-default" onclick="location.href='bookingday/<?php echo $id_space ?>/dayafter'"> > </button>
			<button type="submit" class="btn btn-default" onclick="location.href='bookingday/<?php echo $id_space ?>/today'"><?php echo  BookingTranslator::Today($lang) ?></button>
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
			<a style="color:#333;" href="bookingday/<?php echo $id_space ?>" ><?php echo  BookingTranslator::Day($lang) ?></a>
			</div>
			<div class="btn btn-default" type="button">
				<a style="color:#333;" href="bookingdayarea/<?php echo $id_space ?>" ><?php echo  BookingTranslator::Day_Area($lang) ?></a>
			</div>
			<div class="btn btn-default" type="button">
				<a style="color:#333;" href="bookingweek/<?php echo $id_space ?>" ><?php echo  BookingTranslator::Week($lang) ?></a>
			</div>
			<div class="btn btn-default" type="button">
				<a style="color:#333;" href="bookingweekarea/<?php echo $id_space ?>" ><?php echo  BookingTranslator::Week_Area($lang) ?></a>
			</div>
			<div class="btn btn-default" type="button">
				<a style="color:#333;" href="bookingmonth/<?php echo $id_space ?>" ><?php echo  BookingTranslator::Month($lang) ?></a>
			</div>
			
		</div>
	</div>
</div>

<?php 
$day_begin = $this->clean($scheduling['day_begin']);
$day_end = $this->clean($scheduling['day_end']);
$size_bloc_resa = $this->clean($scheduling['size_bloc_resa']);

?>

<!-- hours column -->
<div class="row">
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
				$heightCol = 2*$agendaStyle["line_height"] . "px";
			}
			else if($size_bloc_resa == 3600){
				$heightCol = $agendaStyle["line_height"] . "px";
			}
		?>
		
		
		
		<div  id="tcell" style="text-align: center; height: <?php echo $heightCol?>; background-color: <?php echo $agendaStyle["header_background"]?>; color: <?php echo $agendaStyle["header_color"]?>; font-size: <?php echo  $agendaStyle["header_font_size"]?>px">
		<?php echo $h?>:00
		</div>
		<?php 	
		}
		?>
	</div>	
	
	<!-- hours reservation -->	
	<div class="col-xs-11" id="colDiv">

		<div id="tcelltop" style="height: <?php echo $agendaStyle["header_height"]?>px; background-color: <?php echo $agendaStyle["header_background"]?>; color: <?php echo $agendaStyle["header_color"]?>; font-size: <?php echo  $agendaStyle["header_font_size"]?>px">
		<p class="text-center"><strong><?php echo  $this->clean($resourceBase['name']) ?></b><br/><?php echo  $this->clean($resourceBase['description']) ?></strong>
			<?php
				if($resourceBase['last_state'] != ""){
					?>
						<br/>
						<a class="btn btn-xs" href="resourcesevents/<?php echo $id_space ?>/<?php echo $resourceBase['id'] ?>" style="background-color:<?php echo $resourceBase['last_state'] ?> ; color: #fff; width:12px; height: 12px;"></a>
					<?php
				}
				?>
		</p>
		</div>

		<?php 
		$isAvailableDay = false;
		$available_days = $scheduling["is_monday"] . "," . $scheduling["is_tuesday"]. "," . $scheduling["is_wednesday"]. "," . $scheduling["is_thursday"]. "," . $scheduling["is_friday"]. "," . $scheduling["is_saturday"]. "," . $scheduling["is_sunday"];
		$available_days = explode(",", $available_days);
		if ($available_days[$day_position-1] == 1){
			$isAvailableDay = true;
		}
		
		bookday($id_space, $size_bloc_resa, $date_unix, $day_begin, $day_end, $calEntries, $isUserAuthorizedToBook, $isAvailableDay, $agendaStyle);
		?>
		
	</div>
</div>

<div class="row" style="background-color: #ffffff;">
	<div class="col-sm-12">
		<?php include "Modules/booking/View/colorcodenavbar.php"; ?>
	</div>
</div>

<?php endblock();

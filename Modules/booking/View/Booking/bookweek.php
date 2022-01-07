<?php include 'Modules/booking/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<?php 
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/View/Booking/bookfunction.php';
		
$available_days = $scheduling["is_monday"] . "," . $scheduling["is_tuesday"]. "," . $scheduling["is_wednesday"]. "," . $scheduling["is_thursday"]. "," . $scheduling["is_friday"]. "," . $scheduling["is_saturday"]. "," . $scheduling["is_sunday"];
$available_days = explode(",", $available_days);
	 
$dayWidth = 0;
for($c = 0 ; $c < count($available_days) ; $c++){
	if ($available_days[$c] > 0){
		$dayWidth++;
	}
}
if ($dayWidth != 0) {
	$dayWidth = 100/$dayWidth;
}

?>

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
	overflow: hidden;
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

<!-- Add the table title -->
<div class="row" style="background-color: #ffffff; padding-top: 12px;">
	<div class="col-sm-10 col-sm-offset-1">
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

<div class="row"  style="background-color: #ffffff; padding-bottom: 12px;">

	<div class="col-md-6 text-left">
		<div class="btn-group" role="group" aria-label="...">
	<button type="submit" class="btn btn-default" onclick="location.href='bookingweek/<?php echo $id_space ?>/dayweekbefore'">&lt;</button>
	<button type="submit" class="btn btn-default" onclick="location.href='bookingweek/<?php echo $id_space ?>/dayweekafter'">></button>
	<button type="submit" class="btn btn-default" onclick="location.href='bookingweek/<?php echo $id_space ?>/thisWeek'"><?php echo  BookingTranslator::This_week($lang) ?></button>
		</div>
		<?php 
	$d = explode("-", $mondayDate);
	$time = mktime(0,0,0,$d[1],$d[2],$d[0]);
	$dayStream = date("l", $time);
	$monthStream = date("F", $time);
	$dayNumStream = date("d", $time);
	$yearStream = date("Y", $time);
	$sufixStream = date("S", $time);

	?>
	<b> <?php echo  BookingTranslator::DateFromTime($time, $lang) ?> -  </b>
	<?php 
	$d = explode("-", $sundayDate);
	$time = mktime(0,0,0,$d[1],$d[2],$d[0]);
	$dayStream = date("l", $time);
	$monthStream = date("F", $time);
	$dayNumStream = date("d", $time);
	$yearStream = date("Y", $time);
	$sufixStream = date("S", $time);

	?>
	<strong><?php echo  BookingTranslator::DateFromTime($time, $lang) ?> </strong>

	</div>
		
	<div class="col-md-6 text-right">
		<div class="btn-group" role="group" aria-label="...">
			<div class="btn btn-default" type="button">
				<a style="color:#333;" href="bookingday/<?php echo $id_space ?>" ><?php echo  BookingTranslator::Day($lang) ?></a>
			</div>
			<div class="btn btn-default " type="button">
				<a style="color:#333;" href="bookingdayarea/<?php echo $id_space ?>" ><?php echo  BookingTranslator::Day_Area($lang) ?></a>
			</div>
			<div class="btn btn-default active" type="button">
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
<div class="col-sm-12">
<div class="col-sm-1" id="colDiv">

<?php 
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
	<div id="tcelltop" style="height: <?php echo $agendaStyle["header_height"]+50 ?>px; background-color:<?php echo $agendaStyle["header_background"]?>; color: <?php echo  $agendaStyle["header_color"]?>"></div> <!-- For the resource title space -->
	
	<?php 
	// Hours
	for ($h = $day_begin ; $h < $day_end ; $h++){

		?>
	
		<div id="tcell" style="text-align: center; height: <?php echo  $heightCol ?>; background-color: <?php echo $agendaStyle["header_background"]?>; color: <?php echo $agendaStyle["header_color"]?>; font-size: <?php echo  $agendaStyle["header_font_size"]?>px">
		<?php echo $h?>:00
		</div>
	<?php 	
	}
	?>	
</div>	
	
<!-- hours reservation -->
<?php
	if (!empty($resourceInfo)) {
?>

	<div class="col-sm-11" id="colDiv">

		<div id="tcelltop" style="width:100%; height: <?php echo $agendaStyle["header_height"] ?>px; background-color:<?php echo  $agendaStyle["header_background"]?>; color: <?php echo $agendaStyle["header_color"]?>">
		<p class="text-center"><strong><?php echo  $this->clean($resourcesBase['name']) ?></strong><br/><?php echo  $this->clean($resourcesBase['description']) ?>
			<?php
				if($resourcesBase['last_state'] != ""){
					?>
						<br/>
						<a class="btn btn-xs" href="resourcesevents/<?php echo $id_space ?>/<?php echo $resourcesBase['id'] ?>" style="background-color:<?php echo $resourcesBase['last_state'] ?> ; color: #fff; width:12px; height: 12px;"></a>
					<?php
				}
				?>
			</p>
		</div>

		
		<div class="row seven-cols">
		
		<?php 
		for ($d = 0 ; $d < 7 ; $d++){
			
			// test if the day is available
			$isDayAvailable = false;
			if ($available_days[$d] == 1){
				$isDayAvailable = true;
			
			
				$idcss = "colDiv";
				if ($d == 0){
					$idcss = "colDivleft";
				}
				if ($d == 6){
					$idcss = "colDivright";
				}
				
				// day title
				$temp = explode("-", $mondayDate);
				$date_unix = mktime(0,0,0,$temp[1], $temp[2]+$d, $temp[0]);
				$dayStream = date("l", $date_unix);
				$monthStream = date("M", $date_unix);
				$dayNumStream = date("d", $date_unix);
				$sufixStream = date("S", $date_unix);
				
				$dayTitle = BookingTranslator::DateFromTime($date_unix, $lang);
				//$dayTitle = $dayStream . " " . $monthStream . ". " . $dayNumStream . $sufixStream;
				
				?>
				
				
				<div class="col-lg-1 col-md-3 col-sm-4 col-xs-6" id="<?php echo  $idcss ?>">
				
				<div id="tcelltop" style="height: 50px; background-color:<?php echo  $agendaStyle["header_background"]?>; color: <?php echo $agendaStyle["header_color"]?>">
				<p class="text-center"><strong> <?php echo  $dayTitle ?></strong> </p>
				</div>
				
				<?php 
				bookday($id_space, $size_bloc_resa, $date_unix, $day_begin, $day_end, $calEntries, $isUserAuthorizedToBook, $isDayAvailable, $agendaStyle);
				?>
				
				</div>
					<?php
			} 
		}
		?>
		</div>
		
	</div>

<?php	
	}
?>
</div>
<div class="row">
	<div class="col-sm-12">
	<?php include "Modules/booking/View/colorcodenavbar.php"; ?>
	</div>
</div>


<?php endblock();

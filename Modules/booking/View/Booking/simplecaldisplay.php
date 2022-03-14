<?php

require_once 'Modules/core/Model/CoreTranslator.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

$modelBookingSetting = new BkBookingSettings();
$modelBookingSupplemetary = new BkCalSupInfo();


$day_begin = $this->clean($scheduling['day_begin']);
$day_end = $this->clean($scheduling['day_end']);
$size_bloc_resa = $this->clean($scheduling['size_bloc_resa']);

$available_days = $scheduling["is_monday"] . "," . $scheduling["is_tuesday"] . "," . $scheduling["is_wednesday"] . "," . $scheduling["is_thursday"] . "," . $scheduling["is_friday"] . "," . $scheduling["is_saturday"] . "," . $scheduling["is_sunday"];
$available_days = explode(",", $available_days);

$colHeader = [];
$calData = [];
$calResources = [];
$calDays = [];

$q = '?';
if(!empty($from)) {
	$elts = implode(':', $from);
	$q .= "from=$elts";
}

for ($d = 0 ; $d < $nbDays ; $d++){
	// day title
	$temp = explode("-", $startDate);
	$date_unix = mktime(0,0,0,$temp[1], $temp[2]+$d, $temp[0]);
	$date_next = $date_unix + 60 * 60 * 24;
	$dayStream = date("l", $date_unix);
	$monthStream = date("M", $date_unix);
	$dayNumStream = date("d", $date_unix);
	$sufixStream = date("S", $date_unix);
		
	$calData[$dayStream] = [];
	for($r = 0 ; $r < count($resourcesBase) ; $r++){
		$cals = [];
		foreach($calEntries[$r] as $c) {
			if($c['end_time'] < $date_unix || $c['start_time'] >= $date_next) {
				continue;
			}
			$cals[] = $c;
		}
		$calData[$dayStream][$resourcesBase[$r]['id']] = $cals;

		$calResources[$resourcesBase[$r]['id']] = $resourcesBase[$r];
		$calDays[$dayStream] = date('Y-m-d', $date_unix);
	}
}

?>

<style>
td {
    border: solid 1px !important;
}
th {
    border: solid 1px !important;
}
</style>
<div class="table-responsive">
<table aria-label="bookings day view" class="table table-sm">
<thead>
	<tr>
	<th scope="col"></th>
	<?php
		$days = array_keys($calData);
	?>
	<?php foreach ($days as $calDay) {  ?>
		<th scope="col" id="<?php echo $calDay?>"><?php echo $calDay.' '.CoreTranslator::dateFromEn($calDays[$calDay],$lang) ?> </th>
	<?php } ?>
	</tr>
</thead>
<tbody>
<tr>

	<?php
	
	foreach($calResources as $resId => $resource) {

		?>
		<tr>
		<th scope="row" id="res<?php echo $resId ?>" id="resource" style="text-align: center">
		<?php
		echo $resource['name'];
		if($resource['last_state'] != ""){
			echo '<br/><a class="btn btn-xs" href="resourcesevents/'.$id_space.'/'.$resource['id'].'" style="background-color:'.$resource['last_state'].' ; color: #fff; width:12px; height: 12px;"></a>';
		}
		?>
		</th>

<?php
	foreach ($days as $calDay) {

		if(!array_key_exists($resId, $calData[$calDay])){
			echo "<td></td>";
			continue;
		}
	?>

				<?php
					//$style .= 'background-color:'.$hcalEntry['color_bg'].';';
					$style = '';
				?>
					<td>
					<?php foreach($calData[$calDay][$resId] as $hcalEntry) { ?>
						<?php
							$text = date('H:i', $hcalEntry['start_time']).' - '.date('H:i', $hcalEntry['end_time']). ' #'.$hcalEntry['id'];
							$extra = $modelBookingSetting->getSummary($id_space, $hcalEntry["recipient_fullname"], $c['phone'], $hcalEntry['short_description'], $hcalEntry['full_description'], false, $context['role']);
							$extra .= $modelBookingSupplemetary->getSummary($id_space ,$hcalEntry["id"]);
							if($extra) {
								$text .= '<br/>'.$extra;
							}
							$hcalEntry['text'] = $text;
							$hcalEntry['link'] = "bookingeditreservation/". $id_space ."/r_" . $hcalEntry['id'].$q;
						?>
						<div class="text-center tcellResa"  style="margin-bottom: 2px; border-radius: 30px; background-color:<?php echo $hcalEntry['color_bg']?>; ">
						<a class="text-center" style="color:<?php echo $hcalEntry['color_text']?>; font-size: <?php echo $agendaStyle["resa_font_size"] ?>px;" href="<?php echo $hcalEntry['link'] ?>"><?php echo $hcalEntry['text']; ?>
							</a>
						</div>
					<?php } ?>

					</td>
				
	<?php } ?>
		</tr>
	<?php } ?>


</tbody>
</tr>

</table>
</div>
<?php

require_once 'Modules/core/Model/CoreTranslator.php';
require_once 'Modules/booking/Model/BookingTranslator.php';


$day_begin = $this->clean($scheduling['day_begin']);
$day_end = $this->clean($scheduling['day_end']);
$size_bloc_resa = $this->clean($scheduling['size_bloc_resa']);

$available_days = $scheduling["is_monday"] . "," . $scheduling["is_tuesday"] . "," . $scheduling["is_wednesday"] . "," . $scheduling["is_thursday"] . "," . $scheduling["is_friday"] . "," . $scheduling["is_saturday"] . "," . $scheduling["is_sunday"];
$available_days = explode(",", $available_days);

$colHeader = [];
$calData = [];
$calResources = [];
$calDays = [];

$nbBlocks = 1;
if ($size_bloc_resa == 900){
	$caseTimeLength = 900;
	$nbBlocks = 4;
} else if($size_bloc_resa == 1800) {
	$caseTimeLength = 1800;
	$nbBlocks = 2;
}

// $schedule = $mschedule->getByResource($id_space, $curentResource);
$rCalendars = [];
if(!$shareCalendar) {
	foreach ($resourcesBase as $r) {
		$mschedule = new BkScheduling();
		$s = $mschedule->getByResource($id_space, $r['id']);

		if($day_begin > $s['day_begin']) {
			$day_begin = $s['day_begin'];
		}
		if($day_end < $s['day_end']) {
			$day_end = $s['day_end'];
		}
		if($size_bloc_resa > $s['size_bloc_resa']) {
			$size_bloc_resa = $s['size_bloc_resa'];
		}
		$rCalendars[$r['id']] = $s;
	}
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


	$isAvailableDay = false;
	if(!$shareCalendar || $scheduling["is_".strtolower($dayStream)] == 1) {
	//if ($available_days[$d] == 1){
		$isAvailableDay = true;
			

		for($r = 0 ; $r < count($resourcesBase) ; $r++){
			$cals = [];
			foreach($calEntries[$r] as $c) {
				if($c['end_time'] < $date_unix || $c['start_time'] >= $date_next) {
					continue;
				}
				$cals[] = $c;
			}
			$colResHeader = compute($id_space, $lang, $size_bloc_resa, $date_unix, $day_begin, $day_end, $cals, $isUserAuthorizedToBook[$r], $isAvailableDay, $agendaStyle, $resourcesBase[$r]['id'], $from, $context['role']);
			foreach($colResHeader as $h => $colData) {
				if(!key_exists($h, $calData)) {
					$calData[$h] = [];
				}
				foreach ($colData as $i => $calEntry) {
					if(!key_exists($calEntry['day'], $calData[$h])) {
						$calData[$h][$calEntry['day']] = [];
					}
					if(!key_exists($calEntry['resource_id'], $calData[$h][$calEntry['day']])) {
						$calData[$h][$calEntry['day']][$calEntry['resource_id']] = [];
					}
					$calData[$h][$calEntry['day']][$calEntry['resource_id']][] = $calEntry;
				}
			}
			$calResources[$resourcesBase[$r]['id']] = $resourcesBase[$r];
            $calDays[$dayStream] = date('Y-m-d', $date_unix);
		}
	}
}

ksort($calData);
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
	<tr><th scope="col"></th>
	<?php
		$calh = array_keys($calData);
		$days = [];
		if(!empty($calh)) {
			$days = $calData[array_keys($calData)[0]];
		}
	?>
	<?php foreach ($days as $calDay => $calRes) { ?>
		<th id="<?php echo $calDay?>" colspan="<?php echo count($calRes) ?>"><?php echo BookingTranslator::translateDayFromEn($calDay, $lang).' '.CoreTranslator::dateFromEn($calDays[$calDay],$lang) ?> </th>
	<?php } ?>
	</tr>
	<tr>
        <th scope="col">Time</th>
        <?php foreach ($days as $calDay => $calRes) { ?>
		<?php foreach($calResources as $resId => $resource) { ?>
		<th colspan="1" id="res<?php echo $resId ?>" id="resource" style="text-align: center">
		<?php
		echo $resource['name'];
		if($resource['last_state'] != ""){
			echo '<br/><a class="btn btn-xs" href="resourcesevents/'.$id_space.'/'.$resource['id'].'" style="background-color:'.$resource['last_state'].' ; color: #fff; width:12px; height: 12px;"></a>';
		}
		?>
		</th>
        <?php } ?>
		<?php } ?>
	</tr>
</thead>
<tbody>
	<?php
	if(empty($calData)) {
		echo "<tr><td>".BookingTranslator::Closed($lang)."</td></tr>";
	}
	foreach ($calData as $i => $calDay) {
			for($e=0;$e<$nbBlocks;$e++) {
	?>
		<tr>
			<?php if($e==0) { ?>
				<th rowspan="<?php echo $nbBlocks ?>"  id="h<?php echo $i?>" class="col-2"><?php echo $i ?>:00</th>
			<?php } ?>
			<?php foreach($calDay as $calDayEntry => $calRes) { ?>
				<?php for($r = 0 ; $r < count($resourcesBase) ; $r++){
					if(!array_key_exists($resourcesBase[$r]['id'], $calRes)) {
						continue;
					}
					$hCalEntries = $calRes[$resourcesBase[$r]['id']];
					$hcalEntry = $hCalEntries[$e];
				?>
					
				<?php
					$style = 'padding: 1px !important;';
					if(!$hcalEntry['text'] && $hcalEntry['expand']) {
						$style .= 'border-top-style: hidden !important;';
					}
					if(!$hcalEntry['free']) { $style .= 'background-color:'.$hcalEntry['color_bg'].';';  }
					$closedByCalendar = false;
					if( !$shareCalendar && (
						$i < $rCalendars[$resourcesBase[$r]['id']]['day_begin'] ||
						$i >= $rCalendars[$resourcesBase[$r]['id']]['day_end'] ||
						$rCalendars[$resourcesBase[$r]['id']]["is_".strtolower($calDayEntry)] == 0
					)) {
						$closedByCalendar = true;
						$style .= 'background-color: darkgray;';
					}
				?>
					<td style="<?php echo $style ?>" headers="<?php echo $calDayEntry ?> res<?php echo $resId ?> h<?php echo $i ?>">
						<?php if (!$closedByCalendar) { ?>
							<?php if($hcalEntry['free']) { ?>
								<?php if ($hcalEntry['link']) { ?>
								<div><a  data-status="free" aria-label="book at <?php echo $hcalEntry['hour'] ?>" class="bi-plus" href="<?php echo $hcalEntry['link'] ?>"></a></div>
								<?php } ?>
							<?php } else { ?>
							<div class="text-center tcellResa"  style="background-color:<?php echo $hcalEntry['color_bg']?>; ">
								<?php if(!$hcalEntry['expand']) { ?>
								<a class="text-center" style="color:<?php echo $hcalEntry['color_text']?>; font-size: <?php echo $agendaStyle["resa_font_size"] ?>px;" href="<?php echo $hcalEntry['link'] ?>"><?php echo $hcalEntry['text']; ?>
								</a>
								<?php } ?>
							</div>
							<?php } ?>
						<?php } ?>
					</td>
				
				<?php } ?>
			<?php 	} ?>
		</tr>
	<?php
			}
	}
	?>
</tbody>
</table>
</div>
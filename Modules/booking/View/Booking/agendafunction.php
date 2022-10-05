<?php

function drawNavigation(string $kind, int $id_space, string $fromDate, ?string $toDate, string $beforeDate, string $afterDate, int|string $bk_id_resource, int|string $bk_id_area, string $id_user, bool $detailedView, string $lang) {
    
    $html = '<div class="m-1 row"  style="background-color: #ffffff; padding-bottom: 12px;">
	<div class="col-12 col-md-7 text-left">
		<div class="btn-group" role="group" aria-label="navigate by '.$kind.'">';

	$today = date("Y-m-d", time());
	$qc = '?'.implode('&', ["bk_curentDate=$fromDate", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
	$qt = '?'.implode('&', ["bk_curentDate=$today", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
	$qb = '?'.implode('&', ["bk_curentDate=$beforeDate", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
	$qa = '?'.implode('&', ["bk_curentDate=$afterDate", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
    $reload = 'booking'.$kind.'/'.$id_space.$qc;

    if(!$detailedView) {
        $qc .= '&view=simple';
        $qt .= '&view=simple';
        $qb .= '&view=simple';
        $qa .= '&view=simple';
    }  else {
        $qc .= '&view=detailed';
        $qt .= '&view=detailed';
        $qb .= '&view=detailed';
        $qa .= '&view=detailed';        
    }

	$html .= '<a id="goback" rel="nofollow" aria-label="previous '.$kind.'" href="booking'.$kind.'/'.$id_space.'/'.$qb.'"><button type="button" class="btn btn-outline-dark"> <span class="bi-arrow-left"></span> </button></a>';
	$html .= '<a id="gonext" rel="nofollow" aria-label="next '.$kind.'" href="booking'.$kind.'/'.$id_space.'/'.$qa.'"><button type="button" class="btn btn-outline-dark"> <span class="bi-arrow-right"></span> </button></a>';
	$html .= '<a id="gocurrent" rel="nofollow" aria-label="current '.$kind.'" href="booking'.$kind.'/'. $id_space.'/'.$qt.'"><button type="button" class="btn btn-outline-dark"> '.BookingTranslator::Today($lang).' </button></a>';
	$html .= '</div>';

	$d = explode("-", $fromDate);
	$time = mktime(0,0,0,$d[1],$d[2],$d[0]);

	$html .= '<strong> '.BookingTranslator::DateFromTime($time, $lang).' </strong>';

	if($toDate) {
    $d = explode("-", $toDate);
	$time = mktime(0,0,0,$d[1],$d[2],$d[0]);
	$html .= ' - <strong> '.BookingTranslator::DateFromTime($time, $lang).' </strong>';
    }

	$html .= '</div>';

    $dayselected = $kind == 'day' ? 'selected':'';
    $dayareaselected = $kind == 'dayarea' ? 'selected':'';
    $weekselected = $kind == 'week' ? 'selected':'';
    $weekareaselected = $kind == 'weekarea' ? 'selected':'';
    $monthselected = $kind == 'month' ? 'selected':'';

    $html .= '<script>function switchView(view) {
        let url = "'.$reload.'" + "&view="+view; 
        window.location.href=url;
    }</script>';

    $html .= '<div class="col-12 col-md-3 text-right">';
    $html .= '<select id="selectview" class="form-select" onchange="location.href=this.value">';
    $html .='			<option aria-label="go to day view" value="bookingday/'.$id_space.$qc.'" '.$dayselected.'>'.BookingTranslator::Day($lang).'</option>';
	$html .= '			<option aria-label="go to day area view" value="bookingdayarea/'.$id_space.$qc.'" '.$dayareaselected.'>'.BookingTranslator::Day_Area($lang).'</option>';
	$html .='			<option aria-label="go to week view" value="bookingweek/'.$id_space.$qc.'" '.$weekselected.' >'.BookingTranslator::Week($lang).'</option>';
	$html .='			<option aria-label="go to week area view" value="bookingweekarea/'.$id_space.$qc.'" '.$weekareaselected.'>'.BookingTranslator::Week_Area($lang).'</option>';
	$html .='			<option aria-label="go to month view" value="bookingmonth/'.$id_space.$qc.'" '.$monthselected.'>'.BookingTranslator::Month($lang).'</option>';
    $html .= '</select>';
    $html .= '</div>';


    if($kind != 'month') {
        $html .='<div class="col-12 col-md-2 text-right">';
        $html .= '           <select onchange="switchView(this.options[this.selectedIndex].value)" class="form-select" aria-label="simple/detailed view" >';
        $simpleSelected = $detailedView ? '' : 'selected';
        $detailedSelected = $detailedView ? 'selected' : '';
        $html .= '              <option '.$simpleSelected.' value="simple">'.BookingTranslator::SimpleView($lang).'</option>';
        $html .= '              <option '.$detailedSelected.' value="detailed">'.BookingTranslator::DetailedView($lang).'</option>';
        $html .= '           </select>';
        $html .= '</div>';
    }

    $html .='
    </div>';
    return $html;

}


function drawAgenda($id_space, $lang, $mois, $annee, $entries, $resourceBase, $agendaStyle, $resourceInfo, $nav=null, $from=[], $role=0) {
	$q = '?';
	if(!empty($from)) {
		$elts = implode(':', $from);
		$q .= "from=$elts";
	}

    $days_fr = ["Lun", "Mar", "Merc", "Jeu", "Ven", "Sam", "Dim"];
    $days_en = ["Mon", "Tue", "Web", "Thu", "Fri", "Sat", "Sun"];

    $month_fr = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Aout", "Septembre", "Octobre", "Novembre", "Décembre"];
    $month_en = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

    $strmonth = $month_en[$mois-1];
    if($lang == "fr") {
        $strmonth = $month_fr[$mois-1];
    }

    $l_day = date("t", mktime(0, 0, 0, $mois, 1, $annee));
    $x = date("N", mktime(0, 0, 0, $mois, 1, $annee));
    $y = date("N", mktime(0, 0, 0, $mois, $l_day, $annee));

    $bk_id_area = $nav['bk_id_area'];
    $bk_id_resource = $nav['bk_id_resource'];
    $id_user = $nav['id_user']

    ?>

    <div class="container">
        <div class="row"><div class="col-12" style="text-align: center"><?php
		echo '<strong>'.$strmonth.'</strong> - '.$resourceBase['name'];
		if($resourceBase['last_state'] != ""){
			echo '<br/><a class="btn btn-xs" href="resourcesevents/'.$id_space.'/'.$resourceBase['id'].'" style="background-color:'.$resourceBase['last_state'].' ; color: #fff; width:12px; height: 12px;"></a>';
		}
		?></div></div>
        <table aria-label="table month view" class="table">
            <thead>
                <?php $day_list = $days_en; if($lang == 'fr') { $day_list = $days_fr; } ?>
                <tr>
                    <?php foreach($day_list as $d) { ?>
                        <th scope="col"><?php echo $d ?></th>
                    <?php } ?>
                </tr>
            </thead>
            <tbody>
            <tr>
                <?php
                $case = 0;
                if ($x > 1) {
                    for ($i = 1; $i < $x; $i++) {
                        echo '<td class="desactive col-1">&nbsp;</td>';
                        $case++;
                    }
                }
                for ($i = 1; $i < ($l_day + 1); $i++) {
                    $y = date("N", mktime(0, 0, 0, $mois, $i, $annee));
                    
                    $tile_date = date("Y-m-d", mktime(0, 0, 0, $mois, $i, $annee));
                    echo "<td class=\"col-1\">";
                    ?>
                <div style="text-align:right; font-size:12px; color:#999999;"> <?php echo $i ?> </div>
                <?php $tileq = '?'.implode('&', ["bk_curentDate=$tile_date", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]); ?>
                <a class="bi-plus" href="bookingday/<?php echo $id_space .'/'.$tile_date.$tileq ?>"></a>
                    <?php
                    $found = false;
                    $modelBookingSetting = new BkBookingSettings();
                    $nbentries = 0;
                    foreach ($entries as $entry) {
                        $dstart = mktime(0, 0, 0, $mois, $i, $annee);
                        $dend = mktime(23, 59, 59, $mois, $i, $annee);
                        if(($entry['start_time'] >= $dstart &&$entry['start_time'] <= $dend) ||
                        ($entry['end_time'] >= $dstart &&$entry['end_time'] <= $dend) ||
                        ($entry['start_time'] < $dstart && $entry['end_time'] > $dend)) {
                            $found = true;
                            $shortDescription = $entry['short_description'];
                            ?>
                        <a href="bookingeditreservation/<?php echo $id_space ?>/r_<?php echo $entry["id"].$q ?>">

                            <div style="background-color: <?php echo $entry['color_bg'] ?>; max-width:200px; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px;" >
                                <p style="border-bottom: thin solid #e1e1e1; font-size:<?php echo $agendaStyle["resa_font_size"] ?>px; color:<?php echo $entry['color_text'] ?>;" >
                                    <?php 
                                        if(date("d", $entry["start_time"]) == $i){
                                            $printStart = date("H:i", $entry["start_time"]);
                                        }
                                        else{
                                            $printStart = "00:00";
                                        }
                                        if(date("d", $entry["end_time"]) == $i){
                                            $printEnd = date("H:i", $entry["end_time"]);
                                        }
                                        else{
                                            $printEnd = "23:59";
                                        }
                                    ?>
                                    <?php echo $printStart . " - " . $printEnd ?></p>
                                    <?php $text = $modelBookingSetting->getSummary($id_space, $entry["recipient_fullname"], $entry['phone'], $entry['client_name'], $shortDescription, $entry['full_description'], true, $role); ?>
                                <p style="font-size:<?php echo $agendaStyle["resa_font_size"] ?>px; color:<?php echo $entry['color_text'] ?>;"><?php echo $text ?></p>
                            </div>
                        </a>
                                <?php
                            $nbentries+=1;
                            }
                            if($nbentries>1) {
                                echo "<div>...</div>";
                                break;
                            }
                        }
                        if (!$found) {
                            ?>
                        <div style="height:45px;"> </div>
                    <?php
                }

                echo "</td>";
                $case++;
                if ($case % 7 == 0) {
                    echo "</tr><tr>";
                }
            }
            if ($y != 7) {
                for ($i = $y; $i < 7; $i++) {
                    echo '<td class="desactive">&nbsp;</td>';
                }
            }
            ?></tr>
            </tbody>
        </table>
    </div>
<?php
}
        
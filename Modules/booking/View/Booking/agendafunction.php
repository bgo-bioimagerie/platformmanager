<?php

function drawNavigation(string $kind, int $id_space, string $fromDate, ?string $toDate, string $beforeDate, string $afterDate, int $bk_id_resource, int $bk_id_area, string $id_user, string $lang) {
    $html = '<div class="row"  style="background-color: #ffffff; padding-bottom: 12px;">
	<div class="col-md-6 text-left">
		<div class="btn-group" role="group" aria-label="navigate by '.$kind.'">';

	$today = date("Y-m-d", time());
	$qc = '?'.implode('&', ["bk_curentDate=$fromDate", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
	$qt = '?'.implode('&', ["bk_curentDate=$today", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
	$qb = '?'.implode('&', ["bk_curentDate=$beforeDate", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
	$qa = '?'.implode('&', ["bk_curentDate=$afterDate", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);

	$html .= '<a aria-label="previous '.$kind.'" href="booking'.$kind.'/'.$id_space.'/'.$qb.'"><button type="button" class="btn btn-default"> <span class="glyphicon glyphicon-menu-left"></span> </button></a>';
	$html .= '<a aria-label="next '.$kind.'" href="booking'.$kind.'/'.$id_space.'/'.$qa.'"><button type="button" class="btn btn-default"> <span class="glyphicon glyphicon-menu-right"></span> </button></a>';
	$html .= '<a aria-label="current '.$kind.'" href="booking'.$kind.'/'. $id_space.'/'.$qt.'"><button type="button" class="btn btn-default"> '.BookingTranslator::Today($lang).' </button></a>';
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

    $dayactive = $kind == 'day' ? 'active':'';
    $dayareaactive = $kind == 'dayarea' ? 'active':'';
    $weekactive = $kind == 'week' ? 'active':'';
    $weekareaactive = $kind == 'weekarea' ? 'active':'';
    $monthactive = $kind == 'month' ? 'active':'';

	$html .= '<div class="col-md-6 text-right">
		<div class="btn-group" role="group">
			<div class="btn btn-default '.$dayactive.'" type="button">';
	$html .='			<a style="color:#333;" href="bookingday/'.$id_space.$qc.'" >'.BookingTranslator::Day($lang).'</a>';
	$html .='		</div>
			<div class="btn btn-default '.$dayareaactive.'" type="button">';
	$html .= '			<a style="color:#333;" href="bookingdayarea/'.$id_space.$qc.'" >'.BookingTranslator::Day_Area($lang).'</a>';
	$html .='		</div>
			<div class="btn btn-default '.$weekactive.'" type="button">';
	$html .='			<a style="color:#333;" href="bookingweek/'.$id_space.$qc.'" >'.BookingTranslator::Week($lang).'</a>';
	$html .='		</div>
			<div class="btn btn-default '.$weekareaactive.'" type="button">';
	$html .='			<a style="color:#333;" href="bookingweekarea/'.$id_space.$qc.'" >'.BookingTranslator::Week_Area($lang).'</a>';
	$html .='		</div>
			<div class="btn btn-default '.$monthactive.'" type="button">';
	$html .='			<a style="color:#333;" href="bookingmonth/'.$id_space.$qc.'" >'.BookingTranslator::Month($lang).'</a>';
	$html .='		</div>
		</div>
    </div>
</div>';
    return $html;

}


function drawAgenda($id_space, $lang, $mois, $annee, $entries, $resourceBase, $agendaStyle, $resourceInfo, $nav=null, $from=[]) {
	$q = '?';
	if(!empty($from)) {
		$elts = implode(':', $from);
		$q .= "from=$elts";
	}
    $mois_fr = Array("", "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");


    $l_day = date("t", mktime(0, 0, 0, $mois, 1, $annee));
    $x = date("N", mktime(0, 0, 0, $mois, 1, $annee));
    $y = date("N", mktime(0, 0, 0, $mois, $l_day, $annee));
    ?>


    <div class="col-xs-12">

        <table class="tableau">
            <caption>
                <div class="col-md-3" style="text-align: left;">
                    <div class="btn-group" role="group" aria-label="navigate by month">
                    <?php
                        $today = date("Y-m-d", time());
                        $qc = $qt = $qb = $qa = '';
                        if($nav){
                            $date = $nav['date'];
                            $beforeDate = $nav['beforeDate'];
                            $afterDate = $nav['afterDate'];
                            $bk_id_area = $nav['bk_id_area'];
                            $bk_id_resource = $nav['bk_id_resource'];
                            $id_user = $nav['id_user'];
                            $qc = '?'.implode('&', ["bk_curentDate=$date", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
                            $qt = '?'.implode('&', ["bk_curentDate=$today", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
                            $qb = '?'.implode('&', ["bk_curentDate=$beforeDate", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
                            $qa = '?'.implode('&', ["bk_curentDate=$afterDate", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]);
                        }
                    ?>
			<a aria-label="previous month" href="bookingmonth/<?php echo "$id_space/$qb" ?>"><button type="button" class="btn btn-default"> <span class="glyphicon glyphicon-menu-left"></span> </button></a>
			<a aria-label="next month" href="bookingmonth/<?php echo "$id_space/$qa" ?>"><button type="button" class="btn btn-default"> <span class="glyphicon glyphicon-menu-right"></span> </button></a>
			<a aria-label="current month" href="bookingmonth/<?php echo "$id_space/$qt" ?>"><button type="button" class="btn btn-default"> <?php echo  BookingTranslator::This_month($lang) ?> </button></a>

                    </div>
                </div>
                <div class="col-md-3">
                    <p ><strong> <?php echo $mois_fr[$mois] . " " . $annee ?></strong></p>
                    <?php
                        if (!empty($resourceInfo)) {
                    ?>
                    <p ><strong> <?php echo $resourceBase["name"] ?></strong></p>
                    <?php
                        }
                    ?>
                </div>
                <div class="col-md-6" style="text-align: right;">
                    <div class="btn-group" role="group" aria-label="...">
                        <div class="btn btn-default" type="button">
                            <a style="color:#333;" href="bookingday/<?php echo $id_space.$qc ?>" ><?php echo BookingTranslator::Day($lang) ?></a>
                        </div>
                        <div class="btn btn-default " type="button">
                            <a style="color:#333;" href="bookingdayarea/<?php echo $id_space.$qc ?>" ><?php echo BookingTranslator::Day_Area($lang) ?></a>
                        </div>
                        <div class="btn btn-default" type="button">
                            <a style="color:#333;" href="bookingweek/<?php echo $id_space.$qc ?>" ><?php echo BookingTranslator::Week($lang) ?></a>
                        </div>
                        <div class="btn btn-default" type="button">
                            <a style="color:#333;" href="bookingweekarea/<?php echo $id_space.$qc ?>" ><?php echo BookingTranslator::Week_Area($lang) ?></a>
                        </div>
                        <div class="btn btn-default active" type="button">
                            <a style="color:#333;" href="bookingmonth/<?php echo $id_space.$qc ?>" ><?php echo BookingTranslator::Month($lang) ?></a>
                        </div> 
                    </div>
                </div>
            </caption>
            <thead>
                <tr><th scope="col">Lun</th><th scope="col">Mar</th><th scope="col">Mer</th><th scope="col">Jeu</th><th scope="col">Ven</th><th scope="col">Sam</th><th scope="col">Dim</th></tr>
            </thead>
            <tbody>
            <tr>
                <?php
                $case = 0;
                if ($x > 1) {
                    for ($i = 1; $i < $x; $i++) {
                        echo '<td class="desactive">&nbsp;</td>';
                        $case++;
                    }
                }
                for ($i = 1; $i < ($l_day + 1); $i++) {
                    $y = date("N", mktime(0, 0, 0, $mois, $i, $annee));
                    $tile_date = date("Y-m-d", mktime(0, 0, 0, $mois, $i, $annee));
                    echo "<td>";
                    ?>
                <div style="text-align:right; font-size:12px; color:#999999;"> <?php echo $i ?> </div>
                <?php $tileq = '?'.implode('&', ["bk_curentDate=$tile_date", "bk_id_resource=$bk_id_resource", "bk_id_area=$bk_id_area", "id_user=$id_user"]); ?>
                <a class="glyphicon glyphicon-plus" href="bookingdayarea/<?php echo $id_space .'/'.$tile_date.$tileq ?>"></a>
                    <?php
                    $found = false;
                    $modelBookingSetting = new BkBookingSettings();
                    
                    foreach ($entries as $entry) {
                        if (date("d", $entry["start_time"]) <= $i && date("d", $entry["end_time"]) >= $i) {
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
                                    <?php $text = $modelBookingSetting->getSummary($id_space, $entry["recipient_fullname"], $entry['phone'], $shortDescription, $entry['full_description'], true); ?>
                                <p style="font-size:<?php echo $agendaStyle["resa_font_size"] ?>px; color:<?php echo $entry['color_text'] ?>;"><?php echo $text ?></p>
                            </div>
                        </a>
                                <?php
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
        
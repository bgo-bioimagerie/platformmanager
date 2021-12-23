<?php

function drawAgenda($id_space, $lang, $mois, $annee, $entries, $resourceBase, $agendaStyle, $resourceInfo) {

    $mois_fr = Array("", "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre");


    $l_day = date("t", mktime(0, 0, 0, $mois, 1, $annee));
    $x = date("N", mktime(0, 0, 0, $mois, 1, $annee));
    $y = date("N", mktime(0, 0, 0, $mois, $l_day, $annee));
    ?>


    <div class="col-xs-12">

        <table class="tableau">
            <caption>
                <div class="col-md-3" style="text-align: left;">
                    <div class="btn-group" role="group" aria-label="...">
                        <button type="button" onclick="location.href = 'bookingmonth/<?php echo $id_space ?>/daymonthbefore'" class="btn btn-default"> &lt; </button>
                        <button type="button" onclick="location.href = 'bookingmonth/<?php echo $id_space ?>/daymonthafter'" class="btn btn-default"> > </button>
                        <button type="button" onclick="location.href = 'bookingmonth/<?php echo $id_space ?>/thisMonth'" class="btn btn-default"><?php echo BookingTranslator::This_month($lang) ?> </button>
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
                            <a style="color:#333;" href="bookingday/<?php echo $id_space ?>" ><?php echo BookingTranslator::Day($lang) ?></a>
                        </div>
                        <div class="btn btn-default " type="button">
                            <a style="color:#333;" href="bookingdayarea/<?php echo $id_space ?>" ><?php echo BookingTranslator::Day_Area($lang) ?></a>
                        </div>
                        <div class="btn btn-default" type="button">
                            <a style="color:#333;" href="bookingweek/<?php echo $id_space ?>" ><?php echo BookingTranslator::Week($lang) ?></a>
                        </div>
                        <div class="btn btn-default" type="button">
                            <a style="color:#333;" href="bookingweekarea/<?php echo $id_space ?>" ><?php echo BookingTranslator::Week_Area($lang) ?></a>
                        </div>
                        <div class="btn btn-default active" type="button">
                            <a style="color:#333;" href="bookingmonth/<?php echo $id_space ?>" ><?php echo BookingTranslator::Month($lang) ?></a>
                        </div> 

                    </div>
                </div>
                </div>
            </caption>
            <tr><th scope="col">Lun</th><th scope="col">Mar</th><th scope="col">Mer</th><th scope="col">Jeu</th><th scope="col">Ven</th><th scope="col">Sam</th><th scope="col">Dim</th></tr>
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
                <a class="glyphicon glyphicon-plus" href="bookingdayarea/<?php echo $id_space .'/'.$tile_date?>"></a>
                    <?php
                    $found = false;
                    $modelBookingSetting = new BkBookingSettings();
                    
                    foreach ($entries as $entry) {
                        if (date("d", $entry["start_time"]) <= $i && date("d", $entry["end_time"]) >= $i) {
                            $found = true;
                            $shortDescription = $entry['short_description'];
                            ?>
                        <a href="bookingeditreservation/<?php echo $id_space ?>/r_<?php echo $entry["id"] ?>">

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
        </table>
    </div>
<?php
}
        
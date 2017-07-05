<?php include 'Modules/booking/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<?php
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/View/Booking/bookfunction.php';

$available_days = $scheduling["is_monday"] . "," . $scheduling["is_tuesday"] . "," . $scheduling["is_wednesday"] . "," . $scheduling["is_thursday"] . "," . $scheduling["is_friday"] . "," . $scheduling["is_saturday"] . "," . $scheduling["is_sunday"];
$available_days = explode(",", $available_days);

$dayWidth = 100 / 8;
?>

<head>

    <style>
        .row {
            display: table;
            width: 100%;
            height:100%;
            border-collapse: collapse;
            overflow:hidden;
        }

        .row-cell{
            margin-bottom: -99999px;
            padding-bottom: 99999px;
        }

        #tcellResa {
            -moz-border-radius: 9px;
            border-radius: 9px;
            border: 1px solid #ffffff;
            font-family: Arial;
            font-size: 9px;
            line-height: 9px;
            letter-spacing: 1px;
            font-weight: normal;
            padding:0px;
            margin:0px;
        }

        #resa_link {
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
                width: <?php echo $dayWidth ?>%;
                *width: <?php echo $dayWidth ?>%;
            }

            a{
                width: 100%;
                color: <?php echo "" . $agendaStyle["header_background"] ?>;
            }
        </style>
    </head>

    <!-- Add the table title -->

    <div class="col-md-12"  style="background-color: #ffffff; padding-top: 12px;">
        <div class="col-md-10 col-md-offset-1">
            <?php
            $message = "";
            if (isset($_SESSION["message"])) {
                $message = $_SESSION["message"];
            }
            ?>
            <?php
            if ($message != ""):
                if (strpos($message, "Err") === false) {
                    ?>
                    <div class="alert alert-success text-center">	
                        <?php
                    } else {
                        ?>
                        <div class="alert alert-danger text-center">
                            <?php
                        }
                        ?>
                        <p><?php echo $message ?></p>
                    </div>
                <?php endif;
                unset($_SESSION["message"])
                ?>
            </div>

            <div class="col-md-12"  style="background-color: #ffffff; padding-bottom: 12px;">

                <div class="col-md-6 text-left">
                    <div class="btn-group" role="group" aria-label="...">
                        <button type="submit" class="btn btn-default"
                                onclick="location.href = 'bookingweekarea/<?php echo $id_space ?>/dayweekbefore'">&lt;</button>
                        <button type="submit" class="btn btn-default"
                                onclick="location.href = 'bookingweekarea/<?php echo $id_space ?>/dayweekafter'">></button>
                        <button type="submit" class="btn btn-default"
                                onclick="location.href = 'bookingweekarea/<?php echo $id_space ?>/thisWeek'"><?php echo BookingTranslator::This_week($lang) ?></button>
                    </div>            
                    <?php
                    $d = explode("-", $mondayDate);
                    $time = mktime(0, 0, 0, $d [1], $d [2], $d [0]);
                    $dayStream = date("l", $time);
                    $monthStream = date("F", $time);
                    $dayNumStream = date("d", $time);
                    $yearStream = date("Y", $time);
                    $sufixStream = date("S", $time);
                    ?>
                    <b><?php echo BookingTranslator::DateFromTime($time, $lang) ?>  -  </b>
                    <?php
                    $d = explode("-", $sundayDate);
                    $time = mktime(0, 0, 0, $d [1], $d [2], $d [0]);
                    $dayStream = date("l", $time);
                    $monthStream = date("F", $time);
                    $dayNumStream = date("d", $time);
                    $yearStream = date("Y", $time);
                    $sufixStream = date("S", $time);
                    ?>
                    <b><?php echo BookingTranslator::DateFromTime($time, $lang) ?> </b>

                </div>

                <div class="col-md-6 text-right">
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
                        <div class="btn btn-default active" type="button">
                            <a style="color:#333;" href="bookingweekarea/<?php echo $id_space ?>" ><?php echo BookingTranslator::Week_Area($lang) ?></a>
                        </div>
                        <div class="btn btn-default" type="button">
                            <a style="color:#333;" href="bookingmonth/<?php echo $id_space ?>" ><?php echo BookingTranslator::Month($lang) ?></a>
                        </div>
                    </div>
                </div>
            </div> 

        </div>
        <br></br>

        <!-- hours reservation -->


        <div class="col-md-12" id="colDiv0">

            <!--  Area title -->

            <div class="col-md-2" id="colDiv0">
            </div>
            <div class="col-md-8" id="colDiv0">
                <div style="height: 50px;">
                    <p class="text-center">
                        <b><?php echo $this->clean($areaname) ?></b>
                    </p>
                </div>
            </div>
        </div>	

        <div class="col-md-12">
            <div class="row seven-cols">
                <div class="row-same-height">
                    <?php
                    $resourceCount = - 1;
                    $modelBookingSetting = new BkBookingSettings ();
                    //$moduleProject = new CoreProject ();
                    $ModulesManagerModel = new CoreMenu ();
                    $isProjectMode = false; //$ModulesManagerModel->getDataMenusUserType("projects");

                    for ($i = -1; $i < count($resourcesBase); $i++) {
                        //foreach ( $resourcesBase as $ResourceBase ) {

                        $resourceID = -1;
                        if ($i >= 0) {
                            $resourceID = $resourcesBase [$i] ["id"];
                        }
                        // echo "resource id = " . $resourcesBase[$resourceCount]["id"] . "</br>";
                        // resource title
                        ?>

                        <?php
                        $styleLine = "";
                        $styleLineHeader = "style=\"text-align: center; background-color:" . $agendaStyle["header_background"] . "; border-right: 1px solid #a1a1a1; border-top: 2px solid #a1a1a1; color: " . $agendaStyle["header_color"] . ";\"";
                        if (!($i % 2)) {
                            $styleLine = "style=\"background-color:#e1e1e1; border-right: 1px solid #a1a1a1; border-top: 2px solid #a1a1a1;\"";
                        } else {
                            $styleLine = "style=\"background-color:#ffffff; border-right: 1px solid #a1a1a1; border-top: 2px solid #a1a1a1;\"";
                        }
                        ?>

                        <div class="row"  > <!-- id="colDivglobal" -->

                            <div class="col-lg-12" id="colDiv">
                                <!-- Content of each day -->
                                <div class="">

                                    <!-- Title -->
                                    <?php
                                    if ($i > -1) {
                                        ?>
                                        <div class="col-lg-1 row-cell" <?php echo $styleLineHeader ?> >
                                            <p>
                                                <b><?php echo $this->clean($resourcesBase[$i]['name']) ?></b>
                                                <?php
                                                if ($resourcesBase[$i]['last_state'] != "") {
                                                    ?>
                                                    <br/>                            
                                                    <a class="btn btn-xs" href="resourcesevents/<?php echo $id_space ?>/<?php echo $resourcesBase[$i]['id'] ?>" style="background-color:<?php echo $resourcesBase[$i]['last_state'] ?> ; color: #fff; width:12px; height: 12px;"></a>
                                                    <?php
                                                }
                                                ?>
                                            </p>
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="col-lg-1 row-cell" <?php echo $styleLineHeader ?>>
                                            <p> </p>
                                        </div>
                                        <?php
                                    }
                                    ?>	

                                    <?php
                                    for ($d = 0; $d < 7; $d ++) {
                                        ?>
                                        <?php
                                        if ($i == -1) {
                                            // day title
                                            $temp = explode("-", $mondayDate);
                                            $date_unix = mktime(0, 0, 0, $temp [1], $temp [2] + $d, $temp [0]);
                                            $dayStream = date("l", $date_unix);
                                            $monthStream = date("M", $date_unix);
                                            $dayNumStream = date("d", $date_unix);
                                            $sufixStream = date("S", $date_unix);

                                            $dayTitle = BookingTranslator::DateFromTime($date_unix, $lang);
                                            ?>
                                            <div class="col-lg-1 row-cell" <?php echo $styleLineHeader ?>>

                                                <div id="tcelltop" style="height: 60px;" class="text-center">
                                                    <p class="text-center">
                                                        <b> <?php echo $dayTitle ?> </b>
                                                    </p>
                                                </div>
                                            </div>
                                            <?php
                                        } else {
                                            ?>
                                            <div class="col-lg-1 row-cell" <?php echo $styleLine ?>>
                                                <!-- Print the reservations for the given day -->
                                                <?php
                                                $resourceCount = $i;
                                                $temp = explode("-", $mondayDate);
                                                $date_unix = mktime(0, 0, 0, $temp [1], $temp [2] + $d, $temp [0]);

                                                $day_begin = $this->clean($scheduling ['day_begin']);
                                                $day_end = $this->clean($scheduling ['day_end']);
                                                $size_bloc_resa = $this->clean($scheduling['size_bloc_resa']);

                                                $isDayAvailable = false;
                                                if ($available_days [$d] == 1) {
                                                    $isDayAvailable = true;
                                                }

                                                // add here the reservations
                                                $foundEntry = false;
                                                foreach ($calEntries as $entry) {

                                                    if ($entry ["resource_id"] == $resourceID && $entry['start_time'] <= $date_unix && $entry['end_time'] >= $date_unix) {
                                                        $foundEntry = true;
                                                        // draw entry
                                                        $shortDescription = $entry ['short_description'];
                                                        if ($isProjectMode) {
                                                            $shortDescription = $moduleProject->getProjectName($entry ['short_description']);
                                                        }

                                                        $txtEndTime = date("H:i", $entry ["end_time"]);
                                                        if ($entry['end_time'] - $date_unix > 3600 * 24) {
                                                            $txtEndTime = "23:59";
                                                        }

                                                        $text = "00:00" . " - " . $txtEndTime . "<br />";
                                                        $text .= $modelBookingSetting->getSummary($id_space, $entry ["recipient_fullname"], $entry ['phone'], $shortDescription, $entry ['full_description'], false);
                                                        ?>
                                                        <div class="text-center" id="tcellResa" style="background-color:<?php echo $entry['color_bg'] ?>;"> 
                                                            <a class="text-center" style="color:<?php echo $entry['color_text'] ?>; font-size:<?php echo $agendaStyle["resa_font_size"] ?>" href="bookingeditreservation/<?php echo $id_space ?>/r_<?php echo $entry['id'] ?>">
                                                        <?php echo $text ?>
                                                            </a>
                                                        </div>
                                                        <?php
                                                    }

                                                    if ($entry ["resource_id"] == $resourceID && $entry ["start_time"] >= $date_unix && $entry ["start_time"] <= $date_unix + 86400) {
                                                        $foundEntry = true;
                                                        // draw entry
                                                        $shortDescription = $entry ['short_description'];
                                                        if ($isProjectMode) {
                                                            $shortDescription = $moduleProject->getProjectName($entry ['short_description']);
                                                        }

                                                        $txtEndTime = date("H:i", $entry ["end_time"]);
                                                        if (date("d", $entry ["end_time"]) > date("d", $date_unix)) {
                                                            $txtEndTime = "23:59";
                                                        }

                                                        $text = date("H:i", $entry ["start_time"]) . " - " . $txtEndTime . "<br />";
                                                        $text .= $modelBookingSetting->getSummary($id_space, $entry ["recipient_fullname"], $entry ['phone'], $shortDescription, $entry ['full_description'], false);
                                                        ?>
                                                        <div class="text-center" id="tcellResa" style="background-color:<?php echo $entry['color_bg'] ?>; "> 
                                                            <a class="text-center" style="color:<?php echo $entry['color_text'] ?>; font-size:<?php echo $agendaStyle["resa_font_size"] ?>px;" href="bookingeditreservation/<?php echo $id_space ?>/r_<?php echo $entry['id'] ?>">
                                                        <?php echo $text ?>
                                                            </a>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                // plus button
                                                if ($isDayAvailable) {
                                                    if ($isUserAuthorizedToBook [$resourceCount]) {
                                                        $dateString = date("Y-m-d", $date_unix);

                                                        $styleTxt = "";
                                                        if (!$foundEntry) {
                                                            $styleTxt = "style=\"height: 60px;\"";
                                                        }
                                                        ?>
                                                        <div class="text-center">
                                                            <a class="glyphicon glyphicon-plus"
                                                               href="bookingeditreservation/<?php echo $id_space ?>/t_<?php echo $dateString . "_" . "8" . "_" . $resourceID ?>">
                                                            </a>
                                                        </div>
                                                        <?php
                                                    } else {
                                                        ?>
                                                        <div class="text-center">
                                                            <p></p>
                                                        </div>
                                                        <?php
                                                    }
                                                } else {
                                                    ?>
                                                    <div class="text-center">
                                                        <p></p>
                                                    </div>
                                                    <?php
                                                }
                                                ?>
                                            </div>
                                            <?php
                                        }
                                    }
                                    ?>
                                </div> <!--  seven-cols -->
                            </div> <!-- col11 days --> 
                        </div> 
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-12">

<?php include "Modules/booking/View/colorcodenavbar.php"; ?>

        </div>

        <?php
        endblock();
        
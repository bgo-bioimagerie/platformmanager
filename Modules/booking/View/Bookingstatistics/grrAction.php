<?php include 'Modules/statistics/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="pm-form-short">
    <form role="form" class="form-horizontal" action="bookinggrrstats/<?php echo $id_space ?>"
          method="post" id="statform">

        <div class="page-header">
            <h3>
                <?php echo BookingTranslator::bookinggrrstats($lang) ?> <br> <small></small>
            </h3>
        </div>

        <?php
        if (isset($errorMessage) && $errorMessage != '') {
            ?>
            <div class="alert alert-danger">
                <p><?php echo $errorMessage ?></p>
            </div>
        <?php } ?>

        <div class="form-group ">
            <label class="control-label col-xs-2"><?php echo BookingTranslator::Date_Begin($lang) ?></label>
            <div class="col-xs-10">
                <div class='input-group date form_date_<?php echo $lang ?>'>

                    <?php
                    $date = "";
                    if (isset($searchDate_start)) {
                        $date = CoreTranslator::dateFromEn($searchDate_start, $lang);
                    }
                    ?> 
                    <input type='text' class="form-control" data-date-format="YYYY-MM-DD" name="searchDate_start" id="searchDate_start"
                           value="<?php echo $date ?>" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>
        <div class="form-group ">
            <label class="control-label col-xs-2"><?php echo BookingTranslator::Date_End($lang) ?></label>
            <div class="col-xs-10">
                <div class='input-group date form_date_<?php echo $lang ?>'>
                    <?php
                    $date = "";
                    if (isset($searchDate_end)) {
                        $date = CoreTranslator::dateFromEn($searchDate_end, $lang);
                    }
                    ?> 
                    <input id="test32" type='text' class="form-control" data-date-format="YYYY-MM-DD" name="searchDate_end" 
                           value="<?php echo $date ?>" />
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
            </div>
        </div>

        <div class="form-group ">
            <label for="inputEmail" class="control-label col-xs-2"> Condition </label>
            <div class="col-xs-10">
                <select class="form-control" name="condition_et_ou" >
                    <OPTION value="and" <?php
                    if (isset($condition_et_ou) && $condition_et_ou == 1) {
                        echo "selected=\"selected\"";
                    }
                    ?>> Valide toutes les conditions suivantes </OPTION>
                    <OPTION value="or" <?php
                    if (isset($condition_et_ou) && $condition_et_ou == 0) {
                        echo "selected=\"selected\"";
                    }
                    ?>> Valide au moins une des conditions suivantes </OPTION>
                </select>
            </div>	

        </div>

        <div class="form-group">
            <label for="inputEmail" class="control-label col-xs-2"><?php echo BookingTranslator::query($lang) ?></label>
            <div class="col-xs-10">
                <?php for ($i = 0; $i < 5; $i++) {
                    ?>
                    <div class="col-xs-4">
                        <select class="form-control" name="champ[]" >
                            <?php
                            $checkedArea = "";
                            $checkedRes = "";
                            $checkedC = "";
                            $checkedS = "";
                            $checkedF = "";
                            $checkedRec = "";
                            if ($champ[$i] == "area") {
                                $checkedArea = "selected=\"selected\"";
                            } else if ($champ[$i] == "resource") {
                                $checkedRes = "selected=\"selected\"";
                            } else if ($champ[$i] == "color_code") {
                                $checkedC = "selected=\"selected\"";
                            } else if ($champ[$i] == "short_description") {
                                $checkedS = "selected=\"selected\"";
                            } else if ($champ[$i] == "full_description") {
                                $checkedF = "selected=\"selected\"";
                            } else if ($champ[$i] == "recipient") {
                                $checkedRec = "selected=\"selected\"";
                            }
                            ?>
                            <OPTION value="area" <?php echo $checkedArea ?>> <?php echo BookingTranslator::Area($lang) ?> </OPTION>
                            <OPTION value="resource" <?php echo $checkedRes ?>> <?php echo BookingTranslator::Resource($lang) ?> </OPTION>
                            <OPTION value="color_code" <?php echo $checkedC ?>> <?php echo BookingTranslator::Color_code($lang) ?> </OPTION>
                            <OPTION value="short_description" <?php echo $checkedS ?>> <?php echo BookingTranslator::Short_description($lang) ?> </OPTION>
                            <OPTION value="full_description" <?php echo $checkedF ?>> <?php echo BookingTranslator::Full_description($lang) ?> </OPTION>
                            <OPTION value="recipient" <?php echo $checkedRec ?>> <?php echo BookingTranslator::recipient($lang) ?> </OPTION>
                        </select>

                    </div>
                    <div class="col-xs-4">
                        <select class="form-control" name="type_recherche[]" >
                            <OPTION value="1" <?php
                            if (isset($type_recherche[$i]) && $type_recherche[$i] == 1) {
                                echo "selected=\"selected\"";
                            }
                            ?>> <?php echo BookingTranslator::Contains($lang) ?> </OPTION>
                            <OPTION value="0" <?php
                            if (isset($type_recherche[$i]) && $type_recherche[$i] == 0) {
                                echo "selected=\"selected\"";
                            }
                            ?>> <?php echo BookingTranslator::Does_not_contain($lang) ?> </OPTION>
                        </select>
                    </div>
                    <div class="col-xs-4">
                        <?php
                        $value = "";
                        if (isset($text[$i])) {
                            $value = $text[$i];
                        }
                        ?>
                        <input type="text" class="form-control" name="text[]" value="<?php echo $value ?>" />
                    </div>
                <?php }
                ?>
            </div>
        </div>
        <br>
        <div class="form-group">
            <label class="control-label col-xs-2"><?php echo BookingTranslator::Output($lang) ?></label>
            <div class="col-xs-10">
                <select class="form-control" name="output">
                    <?php
                    if (isset($output)) {
                        
                    }
                    ?>
                    <OPTION value="1" <?php
                    if (isset($output) && $output == 1) {
                        echo "selected=\"selected\"";
                    }
                    ?>> Détails des réservations </OPTION>
                    <OPTION value="2" <?php
                    if (isset($output) && $output == 2) {
                        echo "selected=\"selected\"";
                    }
                    ?>> Résumé statistique </OPTION>
                    <OPTION value="3" <?php
                    if (isset($output) && $output == 3) {
                        echo "selected=\"selected\"";
                    }
                    ?>> Détails des réservations et résumé </OPTION>
                    <OPTION value="4" <?php
                    if (isset($output) && $output == 4) {
                        echo "selected=\"selected\"";
                    }
                    ?>> Fichier CSV des réservations </OPTION>
                    <OPTION value="5" <?php
                    if (isset($output) && $output == 5) {
                        echo "selected=\"selected\"";
                    }
                    ?>> Fichier CSV du résumé </OPTION>
                </select>
            </div>
        </div>	
        <br>
        <div class="form-group">
            <label for="inputEmail" class="control-label col-xs-2">Résumé par (ne concerne que les résumés) :</label>
            <div class="col-xs-10">
                <select class="form-control" name="summary_rq">
                    <?php
                    $checkedC = "";
                    $checkedS = "";
                    $checkedRec = "";

                    if (isset($summary_rq)) {
                        if ($summary_rq == "color_code") {
                            $checkedC = "selected=\"selected\"";
                        } else if ($summary_rq == "short_description") {
                            $checkedS = "selected=\"selected\"";
                        } else if ($summary_rq == "recipient") {
                            $checkedRec = "selected=\"selected\"";
                        }
                    }
                    ?>
                    <OPTION value="recipient" <?php echo $checkedRec ?>> <?php echo BookingTranslator::recipient($lang) ?> </OPTION>
                    <OPTION value="short_description" <?php echo $checkedS ?>> <?php echo BookingTranslator::Short_description($lang) ?> </OPTION>
                    <OPTION value="color_code" <?php echo $checkedC ?>> <?php echo BookingTranslator::Color_code($lang) ?> </OPTION>
                </select>
            </div>
        </div>

        <div class="col-xs-2 col-xs-offset-10" id="button-div">
            <input class="form-control" id="name" type="hidden" name="is_request" value="y"/>
            <input type="submit" class="btn btn-primary" value="<?php echo CoreTranslator::Ok($lang) ?>" />
        </div>
    </form>
</div>

<div class="col-md-10 col-md-offset-2 pm-table-short">

    <?php
    if (isset($table)) {
        ?>
        <table class="table table-striped text-center table-bordered">
            <caption><?php echo count($table) ?> réservations trouvées </caption>

            <thead> <!-- En-tête du tableau -->
                <tr>				
                    <th><?php echo BookingTranslator::Area($lang) ?></th>
                    <th><?php echo BookingTranslator::Resource($lang) ?></th>
                    <th><?php echo BookingTranslator::Short_description($lang) ?></th>
                    <th><?php echo CoreTranslator::Date($lang) ?></th> 
                    <th><?php echo BookingTranslator::Full_description($lang) ?> </th>
                    <th><?php echo BookingTranslator::Color_code($lang) ?> </th>
                    <th><?php echo BookingTranslator::recipient($lang) ?> </th>
                </tr>
            </thead>

            <tbody> <!-- Corps du tableau -->
                <?php
                foreach ($table as $t) {
                    ?>
                    <tr>
                        <td> <?php echo $t["area_name"] ?> </td>
                        <td><?php echo $t["resource"] ?></td>
                        <td><?php echo $t["short_description"] ?></td>

                        <?php
                        $date = "debut : " . date("d/m/Y à H:i", $t["start_time"]) . "<br/>";
                        $date .= "fin : " . date("d/m/Y à H:i", $t["end_time"]) . "<br/>";
                        $date .= "durée : " . ($t["end_time"] - $t["start_time"]) / 60 . " minutes";
                        ?>

                        <td><?php echo $date ?></td>
                        <td><?php echo $t["full_description"] ?></td>
                        <td><?php echo $t["color"] ?></td>
                        <td><?php echo $t["login"] ?></td>
                    </tr>



                    <?php
                }
                ?>

            </tbody>
        </table>
        <?php
    }
    ?>

</div>

<div class="col-md-10 col-md-offset-2" id="pm-table">

    <?php
    if (isset($summaryTable)) {
        ?>
        <table class="table table-striped text-center table-bordered">
            <caption>Résumé </caption>

            <?php
            $countTable = $summaryTable['countTable'];
            $timeTable = $summaryTable['timeTable'];
            $resourcesNames = $summaryTable['resources'];
            $entrySummary = $summaryTable['entrySummary'];
            //print_r($timeTable);
            ?>



            <thead>
            <th></th>
            <?php
            foreach ($resourcesNames as $name) {
                ?>
                <th><?php echo $name ?></th>
                <?php
            }
            ?>

            <th>Total</th>
            </thead>

            <tbody>
                <?php
                $i = -1;
                $totalCG = 0;
                $totalHG = 0;
                foreach ($countTable as $coutT) {
                    $i++;
                    ?>
                    <tr>
                        <th><?php echo $entrySummary[$i] ?></th>
                        <?php
                        $j = -1;
                        $totalC = 0;
                        $totalH = 0;
                        foreach ($coutT as $col) {
                            $j++;
                            ?>

                            <th> (<?php echo $col ?>) <?php echo $timeTable[$entrySummary[$i]][$resourcesNames[$j]] / 3600 ?> </th>
                            <?php
                            $totalC += $col;
                            $totalH += $timeTable[$entrySummary[$i]][$resourcesNames[$j]];
                        }
                        ?>
                        <th>(<?php echo $totalC ?>) <?php echo $totalH / 3600 ?> </th>
                    </tr>
                    <?php
                    $totalCG += $totalC;
                    $totalHG += $totalH;
                }
                ?>

                <tr>
                    <th> Total </th>
                    <?php
                    for ($i = 0; $i < count($resourcesNames); $i++) {
                        // calcualte the sum
                        $sumC = 0;
                        $sumH = 0;
                        for ($x = 0; $x < count($entrySummary); $x++) {
                            $sumC += $countTable[$entrySummary[$x]][$resourcesNames[$i]];
                            $sumH += $timeTable[$entrySummary[$x]][$resourcesNames[$i]];
                        }
                        ?>
                        <th> (<?php echo $sumC ?>) <?php echo $sumH / 3600 ?> </th>
                        <?php
                    }
                    ?>
                    <th> (<?php echo $totalCG ?>) <?php echo $totalHG / 3600 ?> </th>
                </tr>

            </tbody>
        </table> 
        <?php
    }
    ?>

</div>


<?php include "Framework/timepicker_script.php" ?>

<?php
endblock();


<div class="col-12 col-10 text-left">
    <?php
    $firstDay = $year . "-" . $month . "-1";
    $lastDayIdx = date("t", strtotime($firstDay));
    for ($i = 1; $i <= $lastDayIdx; $i++) {
        ?>

        <div style="border-bottom: 1px solid #666;"> 
            <div class="dropdown">
                <span style="text-transform: uppercase; font-weight: bold; color: #666;">
                    <?php echo date("l d F", mktime(0, 0, 0, $month, $i, $year)) ?>
                </span>
                <button class="btn btn-outline-dark btn-sm dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                    <span style="color: #666;" class="bi-plus"></span>

                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                    <li><a id="addnote_<?php echo $year ?>_<?php echo $month ?>_<?php echo $i ?>"><?php echo BulletjournalTranslator::Notes($lang) ?> </a></li>
                    <li><a id="addtask_<?php echo $year ?>_<?php echo $month ?>_<?php echo $i ?>"><?php echo BulletjournalTranslator::Task($lang) ?></a></li>
                    <li><a id="addevent_<?php echo $year ?>_<?php echo $month ?>_<?php echo $i ?>"><?php echo BulletjournalTranslator::Event($lang) ?></a></li>
                </ul>
            </div>
        </div>
        <?php
        $di = $i;
        if ($i < 10) {
            $di = "0" . $i;
        }
        ?>
        <table class="table-hover table-condensed" id="list_<?php echo $year . "-" . $month . "-" . $di ?>">
            <?php
            foreach ($notes as $dnote) {
                //echo "note = " . $dnote["is_month_task"] . "<br/>";
                // echo "note date = " . $dnote["date"] . "<br/>";
                // echo "note is month = " . $dnote["is_month_task"] . "<br/>";
                if ($dnote["is_month_task"] == 0) {
                    //echo "note pass= " . $dnote["date"] . "<br/>";
                    $d = $i;
                    if ($i < 10) {
                        $d = "0" . $i;
                    }
                    //echo "compare to " . $year . "-" . $month . "-" . $d . "<br/>";
                    if ($dnote["date"] == $year . "-" . $month . "-" . $d) {
                        //echo "found <br/>";
                        $typeicon = "bi-x-square-fill";
                        if ($dnote["type"] == 2) {
                            $typeicon = "bi-asterisk";
                            if ($dnote["migrated"] == 1) {
                                $typeicon = "bi-chevron-right";
                            }
                        }
                        if ($dnote["type"] == 3) {
                            $typeicon = "bi-calendar3";
                        }
                        ?>

                        <?php
                        $styleTR = "";
                        if ($dnote["type"] == 2 && $dnote["status"] == 3) {
                            $styleTR = "style=\"text-decoration: line-through;\"";
                        }
                        ?>       

                        <tr id="tableline_<?php echo $dnote["id"] ?>" <?php echo $styleTR ?> >    

                            <?php
                            $priorityVal = "";
                            $cssStatus = "";
                            if ($dnote["type"] == 2) {
                                $priorityVal = $dnote["priority"];
                                $cssStatus = "background-color:#FF8800;";
                                if ($dnote["status"] == 2 || $dnote["status"] == 3) {
                                    $cssStatus = "background-color:#008000;";
                                }
                            }
                            if ($dnote["type"] == 2) {
                                ?>
                                <td id="task_status_<?php echo $dnote["id"] ?>" style="<?php echo $cssStatus ?>"><span></span></td>
                                <?php
                            } else {
                                ?>
                                <td><span></span></td>
                                <?php
                            }
                            ?>

                            <td><?php echo $priorityVal ?></td>
                            <td><span class="<?php echo $typeicon ?>"></span></td>
                            <?php
                            $openlink = "opennote";
                            if ($dnote["type"] == 2) {
                                $openlink = "opentask";
                            } elseif ($dnote["type"] == 3) {
                                $openlink = "openevent";
                            }
                            ?>

                            <td><a style="color:#666; cursor:pointer;" id="<?php echo $openlink ?>_<?php echo $dnote["id"] ?>"> <?php echo $dnote["name"] ?></a></td>
                            <td><button id="collections_<?php echo $dnote["id"] ?>" class="btn btn-sm btn-outline-dark"><?php echo BulletjournalTranslator::Collections($lang) ?></button></td>
                                <?php
                            if ($dnote["type"] == 2) {
                                $editTxt = BulletjournalTranslator::MarkAsDone($lang);
                                if ($dnote["status"] == 2) {
                                    $editTxt = BulletjournalTranslator::ReOpen($lang);
                                }
                                $cancelTxt = BulletjournalTranslator::Cancel($lang);
                                if ($dnote["status"] == 3) {
                                    $cancelTxt = BulletjournalTranslator::ReOpen($lang);
                                }
                                ?>
                                <td><button id="closetask_<?php echo $dnote["id"] ?>" class="btn btn-sm btn-primary"><?php echo $editTxt ?></button></td>
                                <td><button id="canceltask_<?php echo $dnote["id"] ?>" class="btn btn-sm btn-outline-dark"><?php echo $cancelTxt ?></button></td>
                                <?php
                            } else {
                                ?>
                                <td></td>
                                <td></td>
                                <?php
                            }
                            ?>
                        </tr>

                        <?php
                    }
                    ?>

                    <?php
                }
            }
            ?>
        </table>
        <div class="col-12" style="height: 12px;"></div>
        <?php
    }
    ?>
</div>
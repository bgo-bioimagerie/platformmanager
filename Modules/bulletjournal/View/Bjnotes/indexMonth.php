<div class="col-12 col-10" style="border-bottom: 1px solid #666;">
    <div class="col-10">
        <p style="text-transform: uppercase; font-weight: bold; color: #666;">
            <strong>
                <?php echo date("F Y", mktime(0, 0, 0, $month, 1, $year)) ?>
            </strong>
        </p>
    </div>
    <div class="col-2 text-left">
        <div class="dropdown">
            <button class="btn btn-outline-dark btn-sm dropdown-toggle" type="button" id="dropdownMenu1" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                <span style="color: #666;" class="bi-plus"></span>
            </button>
            <ul class="dropdown-menu" aria-labelledby="dropdownMenu1" role="menu">
                <li onclick="addnote('<?php echo $year ?>_<?php echo $month ?>')"><a class="dropdown-item" id="addnote_<?php echo $year ?>_<?php echo $month ?>"><?php echo BulletjournalTranslator::Notes($lang) ?> </a></li>
                <li onclick="addtask('<?php echo $year ?>_<?php echo $month ?>')">><a class="dropdown-item" id="addtask_<?php echo $year ?>_<?php echo $month ?>"><?php echo BulletjournalTranslator::Task($lang) ?></a></li>
                <li onclick="addevent('<?php echo $year ?>_<?php echo $month ?>')">><a class="dropdown-item" id="addevent_<?php echo $year ?>_<?php echo $month ?>"><?php echo BulletjournalTranslator::Event($lang) ?></a></li>
            </ul>
        </div>
    </div>
</div>

<div class="col-12">
    <table role="presentation" aria-label="month list of notes" class="table-hover table-condensed" id="list_<?php echo $year . "-" . $month?>">
        <?php
        foreach ($notes as $dnote) {
            if ($dnote["is_month_task"] == 1) {
                $d = "01";
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
        }
                ?>
    </table>
</div>
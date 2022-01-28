<div class="col-12" style="border-bottom: 1px solid #666;">
    <div class="col-10">
        <p style="text-transform: uppercase; font-weight: bold; color: #666;">
            <b>
                <?php echo date("F Y", mktime(0, 0, 0, $month, 1, $year)) ?>
            </b>
        </p>
    </div>
</div>

<div class="col-12">
    <table class="table-hover table-condensed" id="list_<?php echo $year . "-" . $month ?>">
        <?php
        foreach ($notes as $dnote) {
            if ($dnote["is_month_task"] == 1) {
                $typeicon = "bi-asterisk";

                $styleTR = "";
                if ($dnote["type"] == 2 && $dnote["status"] == 3) {
                    $styleTR = "style=\"text-decoration: line-through;\"";
                }
                ?>       

                <tr id="tableline_<?php echo $dnote["id"] ?>" <?php echo $styleTR ?> >    

                    <?php
                    $priorityVal = $dnote["priority"];
                    $cssStatus = "background-color:#FF8800;";
                    if ($dnote["status"] == 2 || $dnote["status"] == 3) {
                        $cssStatus = "background-color:#008000;";
                    }
                    ?>
                    <td id="task_status_<?php echo $dnote["id"] ?>" style="<?php echo $cssStatus ?>"><span></span></td>


                    <td><?php echo $priorityVal ?></td>
                    <td><span class="<?php echo $typeicon ?>"></span></td>
                    <?php
                    $openlink = "opentask";
                    ?>

                    <td><a style="color:#666; cursor:pointer;" id="<?php echo $openlink ?>_<?php echo $dnote["id"] ?>"> <?php echo $dnote["name"] ?></a></td>
                    <?php
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
                    <td><button id="migratetask_<?php echo $dnote["id"] ?>" class="btn btn-sm btn-warning"><?php echo BulletjournalTranslator::Migrate($lang) ?></button></td>    

                </tr>

                <?php
            }
        }
        ?>
    </table>
</div>
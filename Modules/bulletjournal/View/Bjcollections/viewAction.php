<?php include 'Modules/bulletjournal/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="col-10" id="pm-table">
    
    <div class="col-12 text-center" style="text-transform:uppercase; color:#666;">
        <strong>Collection: <?php echo $collection["name"] ?></strong>
    </div>
    
    <table aria-label="collection list" class="table-hover table-condensed" id="list_<?php echo $year . "-" . $month . "-" . $di ?>">
        <tbody>
            <?php
            foreach ($notes as $dnote) {
                if ($dnote["is_month_task"] == 0) {
                    $d = $i;
                    if ($i < 10) {
                        $d = "0" . $i;
                    }
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
        </tbody>
    </table>
</div>

<?php endblock(); ?>

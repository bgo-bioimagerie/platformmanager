<?php include 'Modules/mailer/View/layout.php' ?>

<?php startblock('stylesheet') ?>
    <link rel="stylesheet" type="text/css" href="externals/dataTables/dataTables.bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="externals/dataTables/fixedColumns.bootstrap.min.css">
    <script src="externals/dataTables/jquery.dataTables.min.js"></script>
    <script src="externals/dataTables/dataTables.bootstrap.min.js"></script>
    <script src="externals/dataTables/dataTables.fixedColumns.min.js"></script>
<?php endblock() ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="container">
    <?php if($role >= $editRole) { ?>
    <div class="row"><div class="col-sm-12">
    <div >
        <form role="form" action="mailersend/<?php echo $id_space ?>" method="post">
            <div class="page-header"> 
                <h1>
                    <?php echo MailerTranslator::mailer($lang) ?>
                    <br> <small></small>
                </h1> 
            </div> 
            <br><br/>
            <div class="form-group">
                <label class="control-label col-xs-2"><?php echo MailerTranslator::From($lang) ?></label>
                <div class="col-xs-10">
                    <input class="form-control" id="from" type="text" name="from" value="<?php echo $from ?>" readonly
                        />
                </div>
            </div>
            <br><br/>
            <div class="form-group">
                <label class="control-label col-xs-2"><?php echo MailerTranslator::To($lang) ?></label>
                <div class="col-xs-10">
                    <select class="form-control" name="to">
                        <OPTION value="all" > all </OPTION>
                        <OPTION value="managers" > <?php echo CoreTranslator::Managers($lang) ?> </OPTION>
                        <?php if($superAdmin) { ?><OPTION value="admins" > <?php echo CoreTranslator::Admins($lang) ?> </OPTION><?php } ?>
                        <?php foreach ($areasList as $area): ?>
                            <?php
                            $areaname = $this->clean($area['name']);
                            $areaId = $this->clean($area['id']);
                            ?>
                            <OPTION value="a_<?php echo $areaId ?>" > <?php echo ResourcesTranslator::Area($lang) . ": " . $areaname ?> </OPTION>
                        <?php endforeach; ?>

                        <?php foreach ($resourcesList as $resourceArea) { ?>
                            <?php foreach ($resourceArea as $resource) { ?>
                                <?php
                                $areaname = $this->clean($resource['name']);
                                $areaId = $this->clean($resource['id']);
                                ?>
                                <OPTION value="r_<?php echo $areaId ?>" > <?php echo ResourcesTranslator::Resource($lang) . ": " . $areaname ?> </OPTION>
        <?php }
    } ?>
                    </select>
                </div>
            </div>
            <br><br />
            <div class="form-group">
                <label for="inputEmail" class="control-label col-xs-2"><?php echo MailerTranslator::Subject($lang) ?></label>
                <div class="col-xs-10">
                    <input class="form-control" id="subject" type="text" name="subject"
                        />
                </div>
            </div>
            <br><br />
            <div class="form-group">
                <label for="inputEmail" class="control-label col-xs-2"><?php echo MailerTranslator::Content($lang) ?></label>
                <div class="col-xs-10">
                    <textarea class="form-control" id="content" name="content">
                    </textarea>
                </div>
            </div>
            <br><br/>
            <div class="form-group">
                <br><br/>
                <div class="col-xs-2 col-xs-offset-10" id="button-div">
                    <input type="submit" class="btn btn-primary" value="<?php echo MailerTranslator::Send($lang) ?>" />
                </div>
            </div>

        </form>
    </div>

    </div></div>
    <?php } ?>
    <div class="row"><div class="col-sm-12">
        <h3>Mails</h3>
        <table id="mails" aria-label="list of mails" class="table"><thead><tr><th scope="col">Date</th><th scope="col">Subject</th><th scope="col">Message</th><th scope="col">Destination</th></tr></thead><tbody>
        <?php foreach($mails as $mail) {?>
            <tr>
                <td><?php echo $mail['created_at']; ?></td>
                <td><?php echo $mail['subject']; ?></td>
                <td><?php echo $mail['message']; ?></td>
                <td><?php echo MailerTranslator::dest($mail['type'], $lang); ?></td>
            </tr>
        <?php } ?>
        </tbody></table>
    </div></div>
</div>



<script>
        $(document).ready(function () {
            $('#mails').DataTable({
                columnDefs: [{targets: 'no-sort', orderable: true, searchable: true}],
            });
        });
</script>
<?php
endblock();
?>

<?php
 include 'Modules/mailer/View/layout.php';
 require_once 'Modules/core/Model/CoreTranslator.php';
 ?>

<?php startblock('meta') ?>
    <meta name="robots" content="noindex" />
<?php endblock() ?>

<?php startblock('stylesheet') ?>
<link rel="stylesheet" type="text/css" href="externals/node_modules/datatables.net-bs5/css/dataTables.bootstrap5.min.css">

<script src="externals/node_modules/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="externals/node_modules/datatables.net-bs5/js/dataTables.bootstrap5.min.js"></script>
<?php endblock() ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="container">
    <?php if($role >= $editRole) { ?>
    <div class="row"><div class="col-12">
    <div >
        <form role="form" action="mailersend/<?php echo $id_space ?>" method="post">
            <div class="page-header"> 
                <h1>
                    <?php echo MailerTranslator::mailer($lang) ?>
                    <br> <small></small>
                </h1> 
            </div> 
            <br><br/>
            <div class="form-group row">
                <label class="control-label col-12 col-md-2"><?php echo MailerTranslator::From($lang) ?></label>
                <div class="col-12 col-md-10">
                    <input class="form-control" id="from" type="text" name="from" value="<?php echo $from ?>" readonly
                        />
                </div>
            </div>
            <br><br/>
            <div class="form-group row">
                <label for="to" class="control-label col-12 col-md-2"><?php echo MailerTranslator::To($lang) ?></label>
                <div class="col-12 col-md-10">
                    <select class="form-control" id="to" name="to">
                        <OPTION value="all" > <?php echo CoreTranslator::Users($lang) ?> </OPTION>
                        <OPTION value="managers" > <?php echo CoreTranslator::Managers($lang) ?> </OPTION>
                        <?php if ($superAdmin) { ?><OPTION value="admins" > <?php echo CoreTranslator::Admins($lang) ?> </OPTION><?php } ?>
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
            <div class="form-group row">
                <label for="subject" class="control-label col-12 col-md-2"><?php echo MailerTranslator::Subject($lang) ?></label>
                <div class="col-12 col-md-10">
                    <input class="form-control" id="subject" type="text" name="subject"
                        />
                </div>
            </div>
            <br><br />
            <div class="form-group row">
                <label for="content" class="control-label col-12 col-md-2"><?php echo MailerTranslator::Content($lang) ?></label>
                <div class="col-12 col-md-10">
                    <textarea class="form-control" id="content" name="content">
                    </textarea>
                </div>
            </div>
            <br><br/>
            <div class="form-group row">
                <br><br/>
                <div class="col-12" id="button-div">
                    <input type="submit" class="btn btn-primary" value="<?php echo MailerTranslator::Send($lang) ?>" />
                </div>
            </div>

        </form>
    </div>

    </div></div>
    <?php } ?>
    <div class="row mt-3"><div class="col-12">
        <h3>Mails</h3>
        <table id="mails" aria-label="list of mails" class="table">
        <thead>
            <tr>
                <th scope="col">Date</th>
                <th scope="col">Subject</th>
                <th scope="col">Message</th>
                <?php if($role >= $editRole) { ?>
                <th scope="col">Destination</th>
                <th scope="col"></th>
                <?php } ?>
                
            </tr>
        </thead>
        <tbody>
        <?php foreach($mails as $mail) {?>
            <tr onclick="show(<?php echo $mail['id']; ?>)">
                <td><?php echo $mail['created_at']; ?></td>
                <td><?php echo $mail['subject']; ?></td>
                <td><?php echo substr(trim($mail['message']), 0, 15); ?>...</td>
                <?php if($role >= $editRole) { ?>
                <td><?php echo MailerTranslator::dest($mail['type'], $lang); ?></td>
                <td><a href="mailer/<?php echo $id_space; ?>/delete/<?php echo $mail['id']; ?>"><button type="button" class="btn btn-danger"><?php echo CoreTranslator::Delete($lang) ?></button></a>
                <?php } ?>
            </tr>
        <?php } ?>
        </tbody></table>
    </div></div>
    <div class="row mt-3">
        <div class="col-12" style="min-height: 50px; border: solid" >
            <div><strong>Message: <span id="msg_subject"></span></strong></div>
            <div><span id="msg"></span></div>
        </div>
    </div>
</div>



<script>
    let mails = <?php echo json_encode($mails); ?>;
    function show(id) {
        console.log('show id')
        if(!mails) {
            return;
        }
        for(let i=0;i<mails.length;i++){
            if(mails[i]['id'] == id) {
                document.getElementById('msg_subject').textContent = mails[i]['subject'];
                document.getElementById('msg').textContent = mails[i]['message'];
            }
        }
    }
        $(document).ready(function () {
            $('#mails').DataTable({
                columnDefs: [{targets: 'no-sort', orderable: true, searchable: true}],
            });
        });
</script>
<?php
endblock();
 ?>

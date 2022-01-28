<?php include 'Modules/mailer/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-form">
    <form role="form" action="mailersend/<?php echo $id_space ?>" method="post">
        <div class="page-header"> 
            <h1>
                <?php echo MailerTranslator::mailer($lang) ?>
                <br> <small></small>
            </h1> 
        </div> 
        <br><br/>
        <div class="form-group">
            <label class="control-label col-2"><?php echo MailerTranslator::From($lang) ?></label>
            <div class="col-10">
                <input class="form-control" id="from" type="text" name="from" value="<?php echo $from ?>" readonly
                       />
            </div>
        </div>
        <br><br/>
        <div class="form-group">
            <label class="control-label col-2"><?php echo MailerTranslator::To($lang) ?></label>
            <div class="col-10">
                <select class="form-control" name="to">
                    <OPTION value="all" > all </OPTION>
                    <OPTION value="managers" > <?php echo CoreTranslator::Managers($lang) ?> </OPTION>
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
            <label for="inputEmail" class="control-label col-2"><?php echo MailerTranslator::Subject($lang) ?></label>
            <div class="col-10">
                <input class="form-control" id="subject" type="text" name="subject"
                       />
            </div>
        </div>
        <br><br />
        <div class="form-group">
            <label for="inputEmail" class="control-label col-2"><?php echo MailerTranslator::Content($lang) ?></label>
            <div class="col-10">
                <textarea class="form-control" id="content" name="content"
                          >
                </textarea>
            </div>
        </div>
        <br><br/>
        <div class="form-group">
            <br><br/>
            <div class="col-2 col-offset-10" id="button-div">
                <input type="submit" class="btn btn-primary" value="<?php echo MailerTranslator::Send($lang) ?>" />
            </div>
        </div>

    </form>
</div>

<?php endblock(); ?>

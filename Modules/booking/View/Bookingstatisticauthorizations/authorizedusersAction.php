<?php include 'Modules/statistics/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-form">
    <div class="col-md-8 col-md-offset-2">
        <form role="form" class="form-horizontal" action="bookingauthorizedusersquery/<?php echo $id_space ?>"
              method="post" id="statform">

            <div class="page-header">
                <h1>
                    <?php echo BookingTranslator::Authorized_users($lang) ?>
                    <br> <small></small>
                </h1>
            </div>

            <div class="form-group">
                <label for="resource_id" class="control-label col-xs-4"><?php echo ResourcesTranslator::Categories($lang) ?></label>
                <div class="col-xs-8">
                    <select class="form-control" name="resource_id" id="resource_id"
                            >
                                <?php
                                foreach ($resourcesCategories as $r) {
                                    $rId = $this->clean($r['id']);
                                    $rName = $this->clean($r['name']);
                                    ?>
                            <OPTION value="<?php echo $rId ?>"> <?php echo $rName ?> </OPTION>
                            <?php
                        }
                        ?>
                    </select>
                </div>
                <div class="checkbox col-xs-8 col-xs-offset-4">
                    <label>
                        <input type="checkbox" name="email"> Email
                    </label>
                </div>

            </div>	
            <div class="col-xs-3 col-xs-offset-9" id="button-div">
                <input type="submit" class="btn btn-primary" value="<?php echo CoreTranslator::Ok($lang) ?>" />
                
            </div>
        </form>
    </div>
</div>

<?php endblock(); ?>

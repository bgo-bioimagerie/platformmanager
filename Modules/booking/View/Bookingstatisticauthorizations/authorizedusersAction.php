<?php include_once 'Modules/statistics/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-form">
    <div class="col-8">
        <form role="form" class="form-horizontal" action="bookingauthorizedusersquery/<?php echo $id_space ?>"
              method="post" id="statform">

            <div class="page-header">
                <h1>
                    <?php echo BookingTranslator::Authorized_users($lang) ?>
                    <br> <small></small>
                </h1>
            </div>

            <div class="row form-group">
                <label for="resource_id" class="control-label col-4"><?php echo ResourcesTranslator::Categories($lang) ?></label>
                <div class="col-8">
                    <select class="form-select" name="resource_id" id="resource_id"
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
            </div>
            <div class="form-check mb-3">
                    <label for="email" class="form-check-label">Email</label>
                    <input id="email" class="form-check-input" type="checkbox" name="email">
            </div>


                <input type="submit" class="btn btn-primary" value="<?php echo CoreTranslator::Ok($lang) ?>" />

        </form>
    </div>
</div>

<?php endblock(); ?>

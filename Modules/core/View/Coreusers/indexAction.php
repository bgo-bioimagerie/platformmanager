<?php include 'Modules/core/View/layout.php' ?>

<!-- body -->
<?php startblock('content') ?>
<div class="row">
    <div class="col-xs-12 col-md-10 col-md-offset-1" id="pm-table">
        <div class="col-md-12">
            <div class="col-md-10">
                <h1>
                    <?php echo CoreTranslator::Users($lang) ?>
                </h1>
            </div>
            <div class="col-md-2" style="margin-top: 20px;">
                <button type="button" onclick="location.href = 'coreusersedit/0'" class="btn btn-primary"><?php echo CoreTranslator::Add_User($lang) ?></button>
            </div>
        </div>
        <div><?php echo CoreTranslator::DeleteUserCaution($lang)?></div>
        <div class="col-md-12">
            <?php echo $tableHtml ?>
        </div>
    </div> <!-- /container -->
</div>
<?php endblock(); ?>

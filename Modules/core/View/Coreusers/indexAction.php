<?php include_once 'Modules/core/View/layout.php' ?>

<!-- body -->
<?php startblock('content') ?>
<div class="container">
        <div class="row">
            <div class="col">
                <h1>
                    <?php echo CoreTranslator::Users($lang) ?>
                </h1>
            </div>
            <div class="col" >
                <button type="button" onclick="location.href = 'coreusersedit/0'" class="btn btn-primary"><?php echo CoreTranslator::Add_User($lang) ?></button>
            </div>
        </div>
        <div class="row"><div class="col-12"><?php echo CoreTranslator::DeleteUserCaution($lang)?></div></div>
        <div class="row">
            <div class="col-12">
                <?php echo $tableHtml ?>
            </div>
        </div>
</div>
<?php endblock(); ?>

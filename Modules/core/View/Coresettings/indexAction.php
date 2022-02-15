<?php include 'Modules/core/View/layout.php' ?>

<?php startblock('content') ?>
<div class="container">
<div class="row">
    <div class="col-12">
            <h2>
                <?php echo CoreTranslator::User_Settings($lang) ?>
            </h2>
    </div>
<?php foreach($modulesControllers as $controller) {
?>
    <div class="col-12 col-4">
        <div class="card">
            <div class="card-header"><?php echo $this->clean($controller["module"]) ?></div>
            <div class="card-body">
                <a href="<?php echo $this->clean($controller["controller"]) ?>">
                    <button
                        type="button"
                        class="btn btn-primary"
                    >
                        <?php echo CoreTranslator::Edit($lang) ?>
                    </button>
                </a>
            </div>
        </div>
    </div>

<?php } ?>
<!-- language settings -->
    <div class="col-12 col-4">
        <div class="card">
            <div class="card-header"><?php echo CoreTranslator::Language($lang) ?></div>
            <div class="card-body">
                <a href="coreuserslanguageedit">
                    <button
                        type="button"
                        class="btn btn-primary"
                    >
                        <?php echo CoreTranslator::Edit($lang) ?>
                    </button>
                </a>
            </div>
        </div>
    </div>




</div>
</div>
<?php endblock(); ?>
<?php include_once 'Modules/core/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="row">
    
    <div class="col-12">
        <h1><?php echo CoreTranslator::Core_configuration($lang) ?></h1>
    </div>
    
    <?php foreach ($forms as $form) { ?>
    <div class="col-12" style="height: 7px;">
        <p></p>
    </div>
    <div class="col-12 pm-form-short">
        <?php echo $form ?>
    </div>
    <?php } ?>
</div>

<?php endblock(); ?>
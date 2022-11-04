<?php include_once 'Modules/core/View/spacelayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="row">

    <div class="col-12">
        <h1><?php echo RatingTranslator::configuration($lang) ?></h1>
    </div>
    
    <?php foreach ($forms as $form) { ?>
    <div class="col-12" style="background-color: #fff; border-radius: 7px; padding: 7px;">
        <?php echo $form ?>
    </div>
    <?php } ?>
</div>

<?php endblock();

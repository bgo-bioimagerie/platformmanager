<?php include_once 'Modules/core/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="row" style="background-color: #e1e1e1;">
    
    <div class="col-12">
        <h1><?php echo DocumentsTranslator::configuration($lang) ?></h1>
    </div>
    
    <?php foreach ($forms as $form) { ?>
    <div class="col-12" style="height: 7px;">
        <p></p>
    </div>
    <div class="col-12" style="background-color: #fff; border-radius: 7px; padding: 7px;">
        <?php echo $form ?>
    </div>
    <?php } ?>
</div>

<?php endblock(); ?>
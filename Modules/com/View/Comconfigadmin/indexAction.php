<?php include_once 'Modules/com/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="row" style="background-color: #e1e1e1; min-height: 2000px;">
    
    <div class="col-12 col-10 offset-1">
        <h1><?php echo ComTranslator::configuration($lang) ?></h1>
    </div>
    
    <?php foreach ($forms as $form) { ?>
    <div class="col-12 col-10 offset-1" style="height: 7px;">
        <p></p>
    </div>
    <div class="col-12 col-10 offset-1" style="background-color: #fff; border-radius: 7px; padding: 7px;">
        <?php echo $form ?>
    </div>
    <?php } ?>
</div>

<?php endblock(); ?>
<?php include 'Modules/core/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="col-12 col-10">
    
    <div class="col-12 col-10 offset-1">
        <h1><?php echo BookingTranslator::configuration($lang) ?></h1>
    </div>
    
    <?php foreach($forms as $form){ ?>
    <div class="col-12 col-10 offset-1" style="height: 7px;">
        <p></p>
    </div>
    <div class="col-12 col-10 offset-1" style="background-color: #fff; border-radius: 7px; padding: 7px;">
        <?php echo $form ?>
    </div>
    <?php } ?>
    
</div>

<?php endblock(); ?>
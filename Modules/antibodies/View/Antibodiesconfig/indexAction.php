<?php include 'Modules/core/View/spacelayout.php' ?>

    
<?php startblock('content') ?>

<div class="col-md-12" style="min-height: 2000px;">

    <div class="col-12 col-md-10 col-md-offset-1">
        <h3><?php echo AntibodiesTranslator::configuration($lang) ?></h3>
    </div>
    
    <?php foreach($forms as $form){ ?>
    <div class="col-12 col-md-10 col-md-offset-1" style="height: 7px;">
        <p></p>
    </div>
    <div class="col-12 col-md-10 col-md-offset-1" style="background-color: #fff; border-radius: 7px; padding: 7px;">
        <?php echo $form ?>
    </div>
    <?php } ?>
</div>

<?php endblock(); ?>
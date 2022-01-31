<?php include 'Modules/core/View/spacelayout.php' ?>

    
<?php startblock('content') ?>

<div class="row" id="pm-content">
    
    <div class="col-xs-12 col-md-10 col-md-offset-1">
        <h1><?php echo StatisticsTranslator::configuration($lang) ?></h1>
    </div>
    
    <?php foreach($forms as $form){ ?>
    <div class="col-xs-12 col-md-10 col-md-offset-1" style="height: 7px;">
        <p></p>
    </div>
    <div class="col-xs-12 col-md-10 col-md-offset-1" style="background-color: #fff; border-radius: 7px; padding: 7px;">
        <?php echo $form ?>
    </div>
    <?php } ?>
</div>

<?php endblock(); ?>
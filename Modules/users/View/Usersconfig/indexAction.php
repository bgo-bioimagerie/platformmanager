<?php include 'Modules/core/View/spacelayout.php' ?>

    
<?php startblock('content') ?>
<div class="container">
<div class="row" style="background-color: #f1f1f1; min-height: 2000px;">

    <div class="col-12">
        <h1><?php echo UsersTranslator::configuration($lang) ?></h1>
    </div>
    
    <?php foreach($forms as $form){ ?>
    <div class="col-12" style="height: 7px;">
        <p></p>
    </div>
    <div class="col-12" style="background-color: #fff; border-radius: 7px; padding: 7px;">
        <?php echo $form ?>
    </div>
    <?php } ?>
</div>
</div>
<?php endblock(); ?>
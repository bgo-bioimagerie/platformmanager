<?php include_once 'Modules/core/View/spacelayout.php' ?>

    
<?php startblock('content') ?>
<div class="container">
<div class="row">

    <div class="col-12">
        <h3><?php echo AntibodiesTranslator::configuration($lang) ?></h3>
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
</div>
<?php endblock(); ?>
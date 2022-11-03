<?php include_once 'Modules/core/View/spacelayout.php' ?>

    
<?php startblock('content') ?>
<div class="container">
<div class="row">

    <div class="col-12">
        <h1><?php echo HelpdeskTranslator::configuration($lang) ?></h1>
    </div>

    <?php
    if ($fromAddress) {
        ?>
        <div class="col-12">
            <div class="alert alert-info" role="alert">
            <p><?php echo  'Helpdesk email: '.$fromAddress ?></p>
            </div>
        </div>
    <?php
    } else {
        ?>
        <div class="col-12">
            <div class="alert alert-danger" role="alert">
            <p><?php echo  'Helpdesk email not configured, please contact administrator!' ?></p>
            </div>
        </div>
    <?php
    }
?>
    
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
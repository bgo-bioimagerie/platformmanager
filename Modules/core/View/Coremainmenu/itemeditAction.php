<?php include_once 'Modules/core/View/layout.php' ?>

    
<?php startblock('content') ?>
<div class="row">
    <div class="col-12 pm-nav">
        <?php include('Modules/core/View/Coremainmenu/navbar.php'); ?>
    </div>

    <div class="col-12">
        <div class="container pm-form" >
        <?php echo $formHtml ?>
        </div>
    </div>
</div>
<?php endblock(); ?>

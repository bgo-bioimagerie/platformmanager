<?php include 'Modules/core/View/layout.php' ?>

    
<?php startblock('content') ?>
<div class="row">
    <div class="col-md-12 pm-nav">
        <?php include('Modules/core/View/Coremainmenu/navbar.php'); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="container pm-table">
            <a class="btn btn-outline-dark" href="coremainmenuedit/0"><?php echo CoreTranslator::NewMainMenu($lang) ?></a>
            <?php echo $tableHtml ?>
        </div>
    </div>
</div>
<?php endblock(); ?>
<?php include 'Modules/core/View/layout.php' ?>

    
<?php startblock('content') ?>
<div class="row">
    <div class="col-12 pm-nav">
        <?php include('Modules/core/View/Coremainmenu/navbar.php'); ?>
    </div>    

    <div class="col-12">
        <div class="container pm-table">
            <a class="btn btn-outline-dark" href="coremainmenuitemedit/0"><?php echo CoreTranslator::NewItem($lang) ?></a>
            <?php echo $tableHtml ?>
        </div>
    </div>
</div>
<?php endblock(); ?>
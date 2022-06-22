<?php include 'Modules/core/View/Corespaceaccess/layout.php' ?>

    
<?php startblock('content') ?>
<h2><?php echo CoreTranslator::Expiring($lang) ?></h2>
<div class="row">
    <div class="col-12" style="height: 7px;">
    </div>
    <div class="col-12">
        <?php echo $tableHtml ?>
    </div>
</div>
<div class="row">
    <div class="col-12">
        <a href="corespaceaccess/<?php echo $id_space ?>/user/expire/run"><button type="button" class="btn btn-primary">Run</button></a>
    </div>
</div>
<?php endblock(); ?>

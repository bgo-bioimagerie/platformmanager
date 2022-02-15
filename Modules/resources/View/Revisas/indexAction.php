<?php include 'Modules/resources/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-table">

    <div class="col-2 offset-8">
        <button type='button' onclick="location.href = 'resourcesexportvisa/<?php echo $id_space ?>'" class="btn btn-primary"><?php echo CoreTranslator::Export($lang) ?></button>
    </div>
    <div class="col-10">
        <?php echo $tableHtml ?>
    </div>
</div>
<?php endblock(); ?>

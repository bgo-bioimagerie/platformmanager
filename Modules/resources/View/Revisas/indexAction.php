<?php include 'Modules/resources/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-10 pm-table">

    <div class="col-md-2 col-md-offset-8">
        <button type='button' onclick="location.href = 'resourcesexportvisa/<?php echo $id_space ?>'" class="btn btn-primary"><?php echo CoreTranslator::Export($lang) ?></button>
    </div>
    <div class="col-md-10">
        <?php echo $tableHtml ?>
    </div>
</div>
<?php
endblock();

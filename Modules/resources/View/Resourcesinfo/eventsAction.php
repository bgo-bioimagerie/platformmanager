<?php include 'Modules/resources/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-table">

    <?php include "Modules/resources/View/Resourcesinfo/edittabs.php" ?>
    <div class="col-md-2" style="margin-top: 20px;">
        <button type="button" onclick="location.href = 'resourceeditevent/<?php echo $id_space . "/" . $id_resource ?>/0'" class="btn btn-primary"><?php echo ResourcesTranslator::Add_event($lang) ?></button>
    </div> 
    <div class="col-10"><p></p></div>
    <div class="col-10">
        <?php echo $tableHtml ?>
    </div>
</div>
<?php endblock(); ?>

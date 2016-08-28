<?php include 'Modules/resources/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-12" id="pm-content">
    <div class="col-md-12"  id="pm-table">

        <?php include "Modules/resources/View/Resources/edittabs.php" ?>
        <div class="col-md-2" style="margin-top: 20px;">
            <button type="button" onclick="location.href = 'resourceeditevent/<?php echo $id_space . "/" . $id_resource ?>/0'" class="btn btn-primary"><?php echo ResourcesTranslator::Add_event($lang) ?></button>
        </div> 
        <div class="col-xs-12"><p></p></div>
        <div class="col-xs-12">
            <?php echo $tableHtml ?>
        </div>
    </div>
</div>
<?php
endblock();

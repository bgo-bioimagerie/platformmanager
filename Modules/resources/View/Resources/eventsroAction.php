<?php include 'Modules/core/View/spacelayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-12" id="pm-content">
    <div class="col-md-12"  id="pm-table">

        <div class="col-md-12">
            <h3><?php echo ResourcesTranslator::Suivi($lang) . ": " . $resourceInfo["name"] ?></h3>
        </div> 
        <div class="col-xs-12"><p></p></div>
        <div class="col-xs-12">
            <?php echo $tableHtml ?>
        </div>
    </div>
</div>
<?php
endblock();

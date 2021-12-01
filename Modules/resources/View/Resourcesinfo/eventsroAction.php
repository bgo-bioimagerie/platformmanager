<?php include 'Modules/core/View/spacelayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

    <div class="pm-table">

        <div class="col-md-10">
            <h3><?php echo ResourcesTranslator::Suivi($lang) . ": " . $resourceInfo["name"] ?></h3>
        </div> 
        <div class="col-xs-10"><p></p></div>
        <div class="col-xs-10">
            <?php echo $tableHtml ?>
        </div>
    </div>
<?php
endblock();

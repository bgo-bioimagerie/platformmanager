<?php include 'Modules/estore/View/layoutsale.php' ?>

<!-- body -->     
<?php startblock('content') ?>

    <div class="col-md-12 pm-form">
        
        <div class="page-header">
            <h3><?php echo EstoreTranslator::EnteredNewSale($lang) ?></h3>
        </div>
        
        <div class="col-md-12">
            <?php echo $formHtml ?>
        </div>
        
        <div class="col-md-12" style="margin-top: 14px;">
            <?php echo $tableHtml ?>
        </div>
        
    </div>
<?php
endblock();


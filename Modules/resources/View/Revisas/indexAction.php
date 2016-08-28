<?php include 'Modules/resources/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-12" id="pm-content">
    
    <div class="col-md-12" id="pm-table">
        
        <div class="col-md-2 col-md-offset-10">
        <button type='button' onclick="location.href='resourcesexportvisa/<?php echo $id_space ?>'" class="btn btn-primary"><?php echo CoreTranslator::Export($lang) ?></button>
        </div>
        <div class="col-md-12">
        <?php echo $tableHtml ?>
        </div>
    </div>
</div>
<?php
endblock();

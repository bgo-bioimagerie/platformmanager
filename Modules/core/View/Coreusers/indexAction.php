<?php include 'Modules/core/View/layout.php' ?>


<!-- body -->     
<?php startblock('content') ?>
<div class="container">
    <div class="col-md-12" style="margin-top: 7px; border-bottom: 1px solid #e1e1e1;">
        <div class="col-md-10">
            <h1>
                <?php echo CoreTranslator::Users($lang) ?>
            </h1>
        </div>  
        <div class="col-md-2" style="margin-top: 20px;">
            <button type="button" onclick="location.href = 'coreusersedit/0'" class="btn btn-primary"><?php echo CoreTranslator::Add_User($lang) ?></button>
        </div> 
    </div>
    <div class="col-md-12">
        <?php echo $tableHtml ?>
    </div>
</div> <!-- /container -->
<?php
endblock();

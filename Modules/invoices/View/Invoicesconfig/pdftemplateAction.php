<?php include 'Modules/core/View/spacelayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-12">


    <div class="col-md-7">
        <div class="col-md-12 pm-table-short">
            <?php echo $formUploadImages ?>
        </div>
        <div class="col-md-12 pm-table-short">
            <?php echo $tableHtml ?>
        </div>
    </div>
    <div class="col-md-5 pm-form">
        <?php echo $formUpload ?>
    </div>


</div>

<?php
endblock();

<?php include_once 'Modules/core/View/spacelayout.php' ?>

    
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
        <?php if ($formDownload) {
            echo $formDownload;
            echo $formPreview;
        }
?>
        <div class="row">
            <div class="col-xs-12 col-lg-12">
                <a href="externals/pfm/templates/quote_template.twig" download="template.twig" target="_blank" rel="noreferrer,noopener"><button style="margin: 10px;" class="btn btn-primary">Download example template</button></a>
            </div>
        </div>
        <?php echo $formUpload ?>
    </div>


</div>

<?php endblock(); ?>

<?php include 'Modules/core/View/spacelayout.php' ?>

    
<?php startblock('content') ?>
<div class="container">
<div class="row">

    <div class="col-12">
        <h1><?php echo QuoteTranslator::configuration($lang) ?></h1>
    </div>
    
    <?php foreach($forms as $form){ ?>
    <div class="col-12" style="height: 7px;">
        <p></p>
    </div>
    <div class="col-12" style="background-color: #fff; border-radius: 7px; padding: 7px;">
        <?php echo $form ?>
    </div>
    <?php } ?>

    <div class="col-xs-12 col-md-10 col-md-offset-1" style="background-color: #fff; border-radius: 7px; padding: 7px;">
        <h3><?php echo QuoteTranslator::PDFTemplate($lang) ?></h3>
        <a class="btn btn-primary" href="/quote/<?php echo $id_space ?>/pdftemplate" ><?php echo CoreTranslator::Edit($lang) ?></a>
    </div>
</div>
</div>
<?php endblock(); ?>
<?php include 'Modules/estore/View/layoutsale.php' ?>

<!-- body -->     
<?php startblock('content') ?>

    <div class="col-md-12 pm-form">
        <?php echo $formHtml ?>
        <a class="btn btn-danger" href="esaleinvoicepdf/<?php echo $id_space ?>/<?php echo $id_sale ?>"><?php echo EstoreTranslator::invoicePDF($lang) ?></a>

    </div>

<?php
endblock();


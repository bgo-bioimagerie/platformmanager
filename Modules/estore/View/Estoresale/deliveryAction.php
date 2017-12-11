<?php include 'Modules/estore/View/layoutsale.php' ?>

<!-- body -->     
<?php startblock('content') ?>


    <div class="col-md-12 pm-form">
        <?php echo $formHtml ?>
        <a class="btn btn-danger" href="esaledeliverypdf/<?php echo $id_space ?>/<?php echo $id_sale ?>"><?php echo EstoreTranslator::DeliveryPaper($lang) ?></a>

    </div>

<?php
endblock();


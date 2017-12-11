<?php include 'Modules/clients/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-12 pm-form-short">
    <?php echo $formHtml ?>
</div>
    <div class="col-md-6 pm-form-short">
        <?php echo $formDeliveryHtml ?>
    </div>
    <div class="col-md-6 pm-form-short">
        <?php echo $formInvoiceHtml ?>
    </div>
<?php
endblock();

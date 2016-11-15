<?php include 'Modules/invoices/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-10" id="pm-content">
    <div class="col-md-12" id="pm-table">
    <div class="col-md-2 col-md-offset-10">
        <button type="button" onclick="location.href = 'bookingpricesowner/<?php echo $id_space ?>'" class="btn btn-primary"><?php echo InvoicesTranslator::OwnerPrice($lang) ?></button>
    </div>
    <div class="col-md-12">
        <?php echo $formHtml ?>
    </div>
    </div>
</div>
<?php
endblock();

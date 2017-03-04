<?php include 'Modules/invoices/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-12 pm-form">
    
    <h3><?php echo InvoicesTranslator::Edit_invoice($lang) . " : " . $invoice["number"] ?></h3>
    
    <h4> <?php echo ServicesTranslator::Orders($lang) ?> </h4>
    
    <?php 
        foreach ($details as $d){
            ?>
            <a href="<?php echo $d[1] ?>"><?php echo $d[0] ?></a>, 
            <?php
        }
    ?>
    
    <h4> <?php echo InvoicesTranslator::Content($lang) ?> </h4>
    
    <?php echo $htmlForm ?>
    
</div>

<?php endblock();
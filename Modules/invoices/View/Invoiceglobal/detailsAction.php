<?php include 'Modules/invoices/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="container">

<h3><a href="invoiceedit/<?php echo $id_space ?>/<?php echo $invoice['id'] ?>">&lt <?php echo InvoicesTranslator::Invoice($lang) ?></a></h3>

<?php echo $table ?>

<?php echo $table2 ?>


</div>

<?php endblock(); ?>
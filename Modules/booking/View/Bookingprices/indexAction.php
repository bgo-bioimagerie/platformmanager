<?php include 'Modules/invoices/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="container pm-form">
    <?php echo $tableHtml ?>
</div>


<!--  *************  -->
<!--  Popup windows  -->
<!--  *************  -->
<link rel="stylesheet" type="text/css" href="Framework/pm_popup.css">
<div id="hider" class="col-12"></div> 
<div id="entriespopup_box" class="pm_popup_box" style="display: none;">
    <div class="col-1 offset-11" style="text-align: right;"><a id="entriesbuttonclose" class="bi-x-circle-fill" style="cursor:pointer;"></a></div>
        <?php echo $formedit ?>
</div> 

<?php include 'Modules/booking/View/Bookingprices/editscript.php';  ?>

<?php endblock(); ?>
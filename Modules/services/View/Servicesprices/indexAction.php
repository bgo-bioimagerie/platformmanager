<?php include 'Modules/invoices/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-form">
    <?php echo $tableHtml ?>
</div>


<!--  *************  -->
<!--  Popup windows  -->
<!--  *************  -->
<link rel="stylesheet" type="text/css" href="Framework/pm_popup.css">
<div id="hider" class="col-xs-12"></div> 
<div id="entriespopup_box" class="pm_popup_box" style="display: none;">
    <div class="col-md-1 col-md-offset-11" style="text-align: right;"><a id="entriesbuttonclose" class="bi-x-circle-fill" style="cursor:pointer;"></a></div>
        <?php echo $formedit ?>
</div> 

<?php include 'Modules/services/View/Servicesprices/editscript.php';  ?>

<?php endblock(); ?>
<?php include 'Modules/services/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-10" id="pm-form">

    <div class="col-md-12">
    <h3> <?php echo $projectName ?> </h3>
    </div>
    
    <?php include 'Modules/services/View/Servicesprojects/projecttabs.php'; ?>

    <button class="btn btn-primary" id="addentrybutton"><?php echo ServicesTranslator::NewEntry($lang) ?></button>
    
    <?php echo $tableHtml ?>
</div>


<!--  *************  -->
<!--  Popup windows  -->
<!--  *************  -->
<link rel="stylesheet" type="text/css" href="Framework/pm_popup.css">
<div id="hider" class="col-xs-12"></div> 
<div id="entriespopup_box" class="pm_popup_box" style="display: none;">
    <div class="col-md-1 col-md-offset-11" style="text-align: right;"><a id="entriesbuttonclose" class="glyphicon glyphicon-remove" style="cursor:pointer;"></a></div>
        <?php echo $formedit ?>
</div> 


<?php include 'Modules/services/View/Servicesprojects/editscript.php';  ?>

<?php
endblock();

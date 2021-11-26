<?php include 'Modules/services/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="pm-form">

    <div class="col-md-12">
    <h3> <?php echo $projectName ?> </h3>
    </div>
    
    <?php include 'Modules/services/View/Servicesprojects/projecttabs.php'; ?>

    <button class="btn btn-primary" id="addentrybutton"><?php echo ServicesTranslator::NewEntry($lang) ?></button>
    
    <?php echo $tableHtml ?>
    
    <div class="col-md-12 text-right">
        <a class="btn btn-primary" href="servicesprojectexport/<?php echo $id_space ?>/<?php echo $id_project ?>" > <?php echo ServicesTranslator::ExportCsv($lang) ?> </a>
        <a class="btn btn-primary" href="servicesinvoiceprojectquery/<?php echo $id_space ?>/<?php echo $id_project ?>" > <?php echo ServicesTranslator::InvoiceIt($lang) ?> </a>
    </div>
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

<?php include 'Modules/services/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-form">

    <div class="col-12">
    <h3> <?php echo $projectName ?> </h3>
    </div>
    
    <div class="col-12">
        <?php include 'Modules/services/View/Servicesprojects/projecttabs.php'; ?>
    </div>

    <button class="btn btn-primary mb-3" id="addentrybutton"><?php echo ServicesTranslator::NewEntry($lang) ?></button>
    
    <?php echo $tableHtml ?>
    
    <div class="col-12 text-right">
        <a class="btn btn-primary" href="servicesprojectexport/<?php echo $id_space ?>/<?php echo $id_project ?>" > <?php echo ServicesTranslator::ExportCsv($lang) ?> </a>
        <a class="btn btn-primary" href="servicesinvoiceprojectquery/<?php echo $id_space ?>/<?php echo $id_project ?>" > <?php echo ServicesTranslator::InvoiceIt($lang) ?> </a>
    </div>
</div>


<!--  *************  -->
<!--  Popup windows  -->
<!--  *************  -->
<link rel="stylesheet" type="text/css" href="Framework/pm_popup.css">
<div id="hider" class="col-12"></div> 
<div id="entriespopup_box" class="pm_popup_box" style="display: none;">
    <div class="col-1 offset-11" style="text-align: right;"><a id="entriesbuttonclose" class="bi-x-circle-fill" style="cursor:pointer;"></a></div>
        <?php echo $formedit ?>
        <script type="module">
            import {DynamicForms} from '/externals/pfm/dynamics/dynamicForms.js';
            let dynamicForms = new DynamicForms();
            let spaceId = <?php echo $id_space?>;
            let sourceId = "formserviceid";
            let targets = [
                {
                    elementId: "formservicequantity",
                    apiRoute: `services/getServiceType/`,
                    activateOnLoad: true
                }
            ];
            dynamicForms.dynamicFields(sourceId, targets, spaceId, true);
        </script>
</div> 


<?php include 'Modules/services/View/Servicesprojects/editscript.php';  ?>

<?php endblock(); ?>

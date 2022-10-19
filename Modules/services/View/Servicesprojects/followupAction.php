<?php include_once 'Modules/services/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-form">

    <div class="col-12">
    <h3> <?php echo $projectName ?> </h3>
    </div>
    
    <div class="col-12">
        <?php include_once 'Modules/services/View/Servicesprojects/projecttabs.php'; ?>
    </div>

    <button class="btn btn-primary mb-3" id="addentrybutton" onclick="addEntryForm()"><?php echo ServicesTranslator::NewEntry($lang) ?></button>
    
    <?php echo $tableHtml ?>
    
    <div class="col-12 text-right">
        <a class="btn btn-primary" href="servicesprojectexport/<?php echo $idSpace ?>/<?php echo $id_project ?>" > <?php echo ServicesTranslator::ExportCsv($lang) ?> </a>
        <a class="btn btn-primary" href="servicesinvoiceprojectquery/<?php echo $idSpace ?>/<?php echo $id_project ?>" > <?php echo ServicesTranslator::InvoiceIt($lang) ?> </a>
    </div>
</div>


<!--  *************  -->
<!--  Popup windows  -->
<!--  *************  -->


<div id="entriespopup_box" class="modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php echo ServicesTranslator::service($lang) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <?php echo $formedit ?>
        <script type="module">
            import {DynamicForms} from '/externals/pfm/dynamics/dynamicForms.js';
            let dynamicForms = new DynamicForms();
            let spaceId = <?php echo $idSpace?>;
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
    </div>
  </div>
</div>

<script>

    function editentry(id) {
        var arrayid = id.split("_");
        showEditEntryForm(<?php echo $idSpace ?>, arrayid[1]);
    }

    function showEditEntryForm(id_space, id) {
        $.post(
            'servicesgetprojectentry/' + id_space + '/' + id,
            {},
            function (data) {
                $('#formprojectentryprojectid').val(data.id_project);
                $('#formprojectentrydate').val(data.date);
                $('#formprojectentryid').val(data.id);
                $('#formserviceid').val(data.id_service);
                $('#formservicequantity').val(data.quantity);
                $('#formservicecomment').val(data.comment);

                let myModal = new bootstrap.Modal(document.getElementById('entriespopup_box'))
                myModal.show();
            },
            'json'
        );
    }

    function addEntryForm() {
        $('#formprojectentryprojectid').val(<?php echo $id_project ?>);
        $('#formprojectentrydate').val("");
        $('#formprojectentryid').val(0);
        $('#formserviceid').val(0);
        $('#formservicequantity').val("");
        $('#formservicecomment').val("");
        let myModal = new bootstrap.Modal(document.getElementById('entriespopup_box'))
        myModal.show();
    }
</script>


<?php endblock(); ?>

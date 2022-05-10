<?php include 'Modules/services/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-form">

    <div class="col-12">
        <h3> <?php echo $projectName ?> </h3>
    </div>

    <div class="col-12">
        <?php include 'Modules/services/View/Servicesprojects/projecttabs.php'; ?>
    </div>

    <?php echo $formHtml ?>
    <script type="module">
        import {DynamicForms} from '/externals/pfm/dynamics/dynamicForms.js';
        let dynamicForms = new DynamicForms();
        let spaceId = <?php echo $id_space?>;
        let sourceId = "id_resp";
        let targets = [
            {
                elementId: "id_user",
                apiRoute: `clientusers/getusers/`,
                activateOnLoad: false,
                addEmptyItem: true
            }
        ];
        dynamicForms.dynamicFields(sourceId, targets, spaceId);
    </script>
</div>

<?php endblock(); ?>

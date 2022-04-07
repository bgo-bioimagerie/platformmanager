<?php include 'Modules/services/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-form">

<?php echo $formHtml ?>
<script type="module">
    import {DynamicForms} from '/externals/pfm/dynamics/dynamicForms.js';
    let dynamicForms = new DynamicForms();
    let spaceId = <?php echo $id_space?>;
    let sourceId = "id_client";
    let targets = [
        {
            elementId: "id_user",
            apiRoute: `clientusers/getusers/`,
            activateOnLoad: true,
            addEmptyItem: true
        }
    ];
    dynamicForms.dynamicFields(sourceId, targets, spaceId);
</script>
</div>

<?php endblock(); ?>

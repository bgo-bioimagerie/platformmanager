<?php include 'Modules/services/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-form">

<?php echo $formHtml ?>
<script type="module">
    import {DynamicForms} from '/externals/pfm/dynamics/dynamicForms.js';
    let dynamicForms = new DynamicForms();
    let spaceId = <?php echo $id_space?>;
    let sourceId = "id_user";
    let targets = [
        {
            elementId: "id_client",
            apiRoute: `clientusers/getclients/`,
            activateOnLoad: true
        }
    ];
    dynamicForms.dynamicFields(sourceId, targets, spaceId);
</script>
</div>

<?php endblock(); ?>

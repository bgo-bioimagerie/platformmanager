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

        let services = [...document.getElementsByName("services[]")];
        let types = [...document.getElementsByName("type[]")];
        let apiRoute = `services/getServiceType/`;
        for (let i=0; i<services.length; i++) {
            services[i].id += i;
            types[i].id += i;
            let sourceId = services[i].id;
            let targets = [
                {
                    elementId: types[i].id,
                    apiRoute: apiRoute,
                    activateOnLoad: true
                }
            ];
            dynamicForms.dynamicFields(sourceId, targets, spaceId);
        }

        // formadd targets
        let formAddName = <?php echo json_encode($formAddName); ?>;
        let sourceItemsName = "services";
        let targetItemsName = "type";
        dynamicForms.manageLineAdd(formAddName, sourceItemsName, targetItemsName, apiRoute, spaceId);

    </script>
</div>

<?php endblock(); ?>

<?php include 'Modules/services/View/layout.php' ?>

<!-- body -->     
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
                apiRoute: `clientusers/getclients/`
            }
        ];
        dynamicForms.dynamicFields(sourceId, targets, spaceId);

        let services = [...document.getElementsByName("services[]")];
        let types = [...document.getElementsByName("type[]")];
        for (let i=0; i<services.length; i++) {
            services[i].id += i;
            types[i].id += i;
            let sourceId = services[i].id;
            let targets = [
                {
                    elementId: types[i].id,
                    apiRoute: `services/getServiceType/`,
                    activateOnLoad: true
                }
            ];
            dynamicForms.dynamicFields(sourceId, targets, spaceId);
        }
    </script>
</div>

<?php endblock();

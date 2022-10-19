<?php include_once 'Modules/invoices/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-form">
    
    <h3><?php echo InvoicesTranslator::Edit_invoice($lang) . " : " . $invoice["number"] ?></h3>
    
    <h4> <?php echo ServicesTranslator::Orders($lang) ?> </h4>
    
    <?php
        foreach ($details as $d) {
            ?>
            <a href="<?php echo $d[1] ?>"><?php echo $d[0] ?></a>, 
            <?php
        }
?>
    
    <h4> <?php echo InvoicesTranslator::Content($lang) ?> </h4>
    
    <?php echo $htmlForm ?>
    <script type="module">
        import {DynamicForms} from '/externals/pfm/dynamics/dynamicForms.js';
        let dynamicForms = new DynamicForms();
        let spaceId = <?php echo $id_space?>;
        let services = [...document.getElementsByName("id_service[]")];
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
        let sourceItemsName = "id_service";
        let targetItemsName = "type";
        dynamicForms.manageLineAdd(formAddName, sourceItemsName, targetItemsName, apiRoute, spaceId);
    </script>
</div>

<?php endblock(); ?>
<?php include 'Modules/booking/View/layoutsettings.php' ?>

    
<?php startblock('content') ?>

<div class="pm-table">
    
    <?php echo $formHtml ?>
    <script type="module">
        import {DynamicForms} from '/externals/pfm/dynamics/dynamicForms.js';
        let dynamicForms = new DynamicForms();
        let spaceId = <?php echo $id_space?>;
        let input1 = "is_invoicing_unit";
        let input2 = "mandatory"
        let rules = [
            function(is_invoicing_unit, mandatory) {
                if (is_invoicing_unit.value == 1 && mandatory.value == 0) {
                    mandatory.value = 1;
                }
            },
            function (is_invoicing_unit, mandatory) {
                if (is_invoicing_unit.value == 1 && mandatory.value == 0)
                    is_invoicing_unit.value = 0;
            }
        ]
        dynamicForms.linkBooleanInputs(input1, input2, rules, true);
    </script>
    
</div>
<?php endblock(); ?>
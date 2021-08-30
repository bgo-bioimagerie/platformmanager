<?php ?>

<script>

    class ResourceBkPricing {
        constructor(resourceId, resourceName, belongingPrices) {
            this.resourceId = resourceId;
            this.resourceName = resourceName;
            this.belongingPrices = belongingPrices;
        }
    }

    $(document).ready(function () {

        class ResourceBkPricing {
            constructor(resourceId, resourceName, belongingPrices) {
                this.resourceId = resourceId;
                this.resourceName = resourceName;
                this.belongingPrices = belongingPrices;
            }
        }

        $("#hider").hide();
        $("#entriesbuttonclose").click(function () {
            $("#hider").hide();
            $('#entriespopup_box').hide();
        });

<?php for ($i = 0; $i < count($resources); $i++) { ?>
            $("#editentry_<?php echo $resources[$i]["id"] ?>").click(function () {
                var strid = this.id;
                var arrayid = strid.split("_");
                showEditEntryForm(<?php echo $id_space ?>, arrayid[1]);
            });
    <?php
}
?>
        /**
         * Gets bookingPrices from BookingpricesApi then calls displayPopup()
         * 
         * @param string id_space
         * @param string id_resource 
         * 
         */
        function showEditEntryForm(id_space, id_resource) {
            const bkPricing = new ResourceBkPricing();
            bkPricing.resourceId = id_resource;
            bkPricing.belongingPrices = [];

            $.post('bookinggetprices/' + id_space + '/' + id_resource)
                .done((response) => {
                    // TODO: errors linked with prometheus returned during tests. Following string manipulations are set to filter it.
                    // Check how to do that properly
                    jsonData = response.includes("<br />") ?  jsonData = response.slice(0, response.indexOf("<br />")) : response;
                    data = JSON.parse(jsonData);

                    // format to be used more easily
                    Object.entries(data).forEach((entry) => {
                        const [key, value] = entry;
                        if (key.slice(0, 4) === "bel_") {
                            bkPricing.belongingPrices.push({belonging: key, price: value});
                        } else if (key === "resource") {
                            bkPricing.resourceName = value;
                        }
                    });
                    displayPopup(bkPricing);
                });
        }

        /**
         * Displays a popup window #entriespopup_box
         * to edit resource prices
         * 
         * @param ResourceBkPricing bkPricing
         * 
         */
        function displayPopup(bkPricing) {
            $('#resource_id').val(bkPricing.resourceId);
            $('#resource').val(bkPricing.resourceName);
            bkPricing.belongingPrices.forEach( (bkPrice) => {
                $('#' + bkPrice.belonging).val(bkPrice.price);
            });
            $("#hider").fadeIn("slow");
            $('#entriespopup_box').fadeIn("slow");
        }
    });
</script>

<?php ?>

<script>

class ServiceBkPricing {
        constructor(serviceId, serviceName, belongingPrices) {
            this.serviceId = serviceId;
            this.serviceName = serviceName;
            this.belongingPrices = belongingPrices;
        }
    }

    $(document).ready(function () {

        $("#hider").hide();
        $("#entriesbuttonclose").click(function () {
            $("#hider").hide();
            $('#entriespopup_box').hide();
        });

<?php for ($i = 0; $i < count($services); $i++) { ?>
            $("#editentry_<?php echo $services[$i]["id"] ?>").click(function () {
                
                var strid = this.id;
                var arrayid = strid.split("_");
                showEditEntryForm(<?php echo $id_space ?>, arrayid[1]);
            });
    <?php
}
?>
        /**
         * Gets bookingPrices from ServicespricesApi then calls displayPopup()
         * 
         * @param string id_space
         * @param string id_service 
         * 
         */
        function showEditEntryForm(id_space, id_service) {
            const bkPricing = new ServiceBkPricing();
            bkPricing.serviceId = id_service;
            bkPricing.belongingPrices = [];
            $.post('servicesgetprices/' + id_space + '/' + id_service)
                .done((response) => {
                    //alert(response);
                    jsonData = response.includes("<br />") ?  jsonData = response.slice(0, response.indexOf("<br />")) : response;
                    data = JSON.parse(jsonData);

                    // format to be used more easily
                    Object.entries(data).forEach((entry) => {
                        const [key, value] = entry;
                        if (key.slice(0, 4) === "bel_") {
                            bkPricing.belongingPrices.push({belonging: key, price: value});
                        } else if (key === "service") {
                            bkPricing.serviceName = value;
                        }
                    });
                    displayPopup(bkPricing);
            });
        }
        /**
         * Displays a popup window #entriespopup_box
         * to edit service prices
         * 
         * @param ServiceBkPricing bkPricing
         * 
         */
        function displayPopup(bkPricing) {
            $('#service_id').val(bkPricing.serviceId);
            $('#service').val(bkPricing.serviceName);
            bkPricing.belongingPrices.forEach( (bkPrice) => {
                $('#' + bkPrice.belonging).val(bkPrice.price);
            });
            $("#hider").fadeIn("slow");
            $('#entriespopup_box').fadeIn("slow");
        }

    });
</script>            
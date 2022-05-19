<?php include 'Modules/invoices/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="container pm-form">
    <?php echo $tableHtml ?>
</div>


<div id="entriespopup_box" class="modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><?php echo ServicesTranslator::Prices($lang) ?></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
      <?php echo $formedit ?>
      </div>
    </div>
  </div>
</div>


<script>

    class ServiceBkPricing {
        constructor(serviceId, serviceName, belongingPrices) {
            this.serviceId = serviceId;
            this.serviceName = serviceName;
            this.belongingPrices = belongingPrices;
        }
    }

    function editentry(id) {
        var arrayid = id.split("_");
        showEditEntryForm(<?php echo $id_space ?>, arrayid[1]);
    }

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
        let myModal = new bootstrap.Modal(document.getElementById('entriespopup_box'))
        myModal.show();
    }


</script>



<?php endblock(); ?>
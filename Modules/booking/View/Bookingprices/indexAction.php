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
    class ResourceBkPricing {
        constructor(resourceId, resourceName, belongingPrices) {
            this.resourceId = resourceId;
            this.resourceName = resourceName;
            this.belongingPrices = belongingPrices;
        }
    }
    function editentry(id) {
        var arrayid = id.split("_");
        showEditEntryForm(<?php echo $id_space ?>, arrayid[1]);
    }

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
        let myModal = new bootstrap.Modal(document.getElementById('entriespopup_box'))
        myModal.show();
    }

</script>

<?php endblock(); ?>
<?php include 'Modules/quote/View/layout.php' ?>

    
<?php startblock('content') ?>

<?php
if ($id_quote > 0) {
?>
    <div class="pm-form-short">
<?php
    } else {
?>
        <div class="pm-form">
<?php } ?>
        <h3><?php echo QuoteTranslator::EditQuote($lang) ?></h3>
        <div class="m-3">
            <a class="btn btn-outline-dark" href="clclientedit/<?php echo $id_space ?>">
                <?php echo ClientsTranslator::NewClient($lang) ?>
            </a>
        </div>
        <?php echo $formHtml ?>
        <script type="module">
            import {DynamicForms} from '/externals/pfm/dynamics/dynamicForms.js';
            let dynamicForms = new DynamicForms();
            let spaceId = <?php echo $id_space?>;
            let sourceId = "id_client";
            let targets = [
                {
                    elementId: "pricing",
                    apiRoute: `clientspricings/getpricing/`
                },
                {
                    elementId: "address",
                    apiRoute: `clientslist/getaddress/`
                }
            ];
            dynamicForms.dynamicFields(sourceId, targets, spaceId);
        </script>
    </div>

    <?php
    if ($tableHtml != "") {
    ?>
        <div class="col-12 pm-form">
            <br/>
            <a class="btn btn-primary" id="additembutton"><?php echo QuoteTranslator::NewItem($lang) ?></a>
            <a class="btn btn-danger" href="quotepdf/<?php echo $id_space.'/'.$id_quote ?>"><?php echo QuoteTranslator::PDF($lang) ?></a>

            <?php echo $tableHtml ?>
        </div>
    <?php
    }
    ?>

    <!--  *************  -->
    <!--  Popup windows  -->
    <!--  *************  -->
    <link rel="stylesheet" type="text/css" href="Framework/pm_popup.css">
    <div id="hider" class="col-12"></div> 
    <div id="itemspopup_box" class="pm_popup_box" style="display: none;">
        <div class="col-1 offset-11" style="text-align: right;"><a id="itemsbuttonclose" class="bi-x-circle-fill" style="cursor:pointer;"></a></div>
            <?php echo $formitemHtml ?>
    </div> 


    <?php include 'Modules/quote/View/Quotelist/editnewscript.php'; ?>
    
<?php endblock(); ?>
    
<?php include 'Modules/quote/View/layout.php' ?>

    
<?php startblock('content') ?>

<?php
if ($id_quote > 0) {
    ?>
    <div class="pm-form-short">
<?php } else { ?>
        <div class="pm-form">
<?php } ?>
        <h3><?php echo QuoteTranslator::EditQuote($lang) ?></h3>
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
        </script>
    </div>

    <?php
        if ($tableHtml != "") {
            ?>
        <div class="col-12 pm-form">
            <br/>
            <a onclick="addItem()" class="btn btn-primary" id="additembutton"><?php echo QuoteTranslator::NewItem($lang) ?></a>
            <a class="btn btn-danger" href="quotepdf/<?php echo $id_space.'/'.$id_quote ?>"><?php echo QuoteTranslator::PDF($lang) ?></a>

            <?php echo $tableHtml ?>
            
        </div>
        <?php
        }
?>

    <div id="entriespopup_box" class="modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
        <?php echo $formitemHtml ?>
        </div>
        </div>
    </div>
    </div>

    <script>


        function editentry(id) {
                var arrayid = id.split("_");
                showEditForm(arrayid[1]);        
        }

       function addItem() {
            $('#id_quote').val(<?php echo $id_quote ?>);
            $('#id').val(0);
            $('#id_item').val(0);
            $('#quantity').val("");
            $('#comment').val("");

            let myModal = new bootstrap.Modal(document.getElementById('entriespopup_box'))
            myModal.show();
        }

        function showEditForm(id) {
            $.post(
                    'quotegetitem/<?php echo $id_space ?>/' + id,
                    {},
                    function (data) {

                        $('#id_quote').val(<?php echo $id_quote ?>);
                        $('#id').val(data.id);
                        $('#id_item').val(data.module + "_" + data.id_content);
                        $('#quantity').val(data.quantity);
                        $('#comment').val(data.comment);

                        let myModal = new bootstrap.Modal(document.getElementById('entriespopup_box'))
                        myModal.show();
                    },
                    'json'
                    );

        }

    </script> 




<?php endblock(); ?>
    
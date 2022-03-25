<?php include 'Modules/invoices/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="container pm-table">
    
    <div class="col-10">
        <div id="invoice-message-div" class="alert alert-success">
            
        </div>
    </div>
    
    <h3><?php echo InvoicesTranslator::Edit_invoice($lang) ?> : <?php echo $invoice["number"] ?> </h3>

    <h4><a href="invoices/<?php echo $id_space ?>/<?php echo $invoice['id'] ?>/details">Details</a></h4>

    <form class="form-horizontal">
        <div id="invoiceform" class="col-12">

            <table class="table" aria-label="list of products">
                <thead>
                    <tr>
                        <th scope="col"><?php echo InvoicesTranslator::Product($lang) ?></th>
                        <th scope="col"><?php echo InvoicesTranslator::Quantity($lang) ?></th>
                        <th scope="col"><?php echo InvoicesTranslator::UnitPrice($lang) ?></th>
                    </tr>
                </thead>
                <tbody id="invoicetablebody">

                </tbody>
            </table>
        </div>
        <div class="col-12">
            <div class="form-group">
                <label class="control-label col-8 text-right"> <?php echo InvoicesTranslator::Discount($lang) ?> </label>
                <div class="col-4">
                    <input id="invoicediscount" class="form-control" type="text" name="" value="<?php echo $invoice["discount"] ?>" />
                </div>
            </div>
            <div class="form-group">
                <label class="control-label col-8 text-right"> <?php echo InvoicesTranslator::Total_HT($lang) ?> </label>
                <div class="col-4">
                    <input id="invoicetotalht" class="form-control" type="text" name="" value="<?php echo $invoice["total_ht"] ?>" />
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="col-12 text-right">
                <button id="invoicevalidate" class="btn btn-primary"><?php echo CoreTranslator::Save($lang) ?></button>
                <a href="invoiceglobalpdf/<?php echo $id_space ?>/<?php echo $invoice["id"] ?>/0" class="btn btn-danger"><?php echo InvoicesTranslator::PDF($lang) ?></a>
                <a href="invoiceglobalpdf/<?php echo $id_space ?>/<?php echo $invoice["id"] ?>/1" class="btn btn-danger"><?php echo InvoicesTranslator::PDFDetails($lang) ?></a>
            </div>
        </div> 
    </form>
</div>

<script>

    $(document).ready(function () {

        $('#invoice-message-div').hide();

        $('form').on('submit', function (e) {
            e.preventDefault();
        });

        // print the data
        var data = JSON.parse('<?php echo $invoiceitem["content"] ?>');

        var html = "";
        var total_ht = 0;
        var datalength = data.length;
        for (var i = 0; i < datalength; i++) {

            module = data[i]["module"];
            content = data[i]["data"]["count"];
            var contentlength = content.length;
            for (var j = 0; j < contentlength; j++) {

                html += '<tr>';
                html += '   <td class="invoicelabel" data-module="' + module + '">' + content[j]["label"] + '</td>';
                html += '   <td><input class="form-control invoicequantity" id="quantity_' + content[j]["label"].split(' ').join('_') + '" type="text" value="' + content[j]["quantity"] + '"/></td>';
                html += '   <td><input class="form-control invoiceprice" id="price_' + content[j]["label"].split(' ').join('_') + '" type="text" value="' + content[j]["unitprice"] + '"/></td>';
                html += '</tr>';

                total_ht += parseFloat(content[j]["quantity"]) * parseFloat(content[j]["unitprice"]);

            }
        }
        total_ht -= $('#invoicediscount').val();
        $('#invoicetablebody').html(html);
        $('#invoicetotalht').attr("value", total_ht.toFixed(2));



        $('.invoicequantity').on('change', function () {
            updateprice();
        });
        $('.invoiceprice').on('change', function () {
            updateprice();
        });
        $('#invoicediscount').on('change', function () {
            updateprice();
        });
        $('#invoicevalidate').click(function () {

            for (var i = 0; i < datalength; i++) {

                content = data[i]["data"]["count"];
                var contentlength = content.length;
                for (var j = 0; j < contentlength; j++) {

                    quantity = $('#quantity_' + content[j]["label"].split(' ').join('_')).val();
                    price = $('#price_' + content[j]["label"].split(' ').join('_')).val();

                    data[i]["data"]["count"][j]["quantity"] = quantity;
                    data[i]["data"]["count"][j]["unitprice"] = price;
                }
            }

            //alert("content = " + JSON.stringify(data) );

            data0 = {discount: $('#invoicediscount').val(), total_ht: $('#invoicetotalht').val(), content: JSON.stringify(data)};

            $.post(
                    "<?php echo $validateURL ?>",
                    data0,
                    function (response) {
                        
                        responseObj = JSON.parse(response);
                        $('#invoice-message-div').html(responseObj.message);
                        $('#invoice-message-div').show();
                    }
            );

        });

    });

    function updateprice() {

        lines = $('#invoicetablebody').find('tr');

        var total_ht = 0;
        lines.each(function () {
            total_ht += parseFloat($(this).find('.invoicequantity').val()) * parseFloat($(this).find('.invoiceprice').val());
        });
        total_ht -= $('#invoicediscount').val();
        $('#invoicetotalht').attr("value", total_ht.toFixed(2));
    }


</script>

<?php endblock(); ?>

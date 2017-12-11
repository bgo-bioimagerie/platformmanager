<?php include 'Modules/estore/View/layoutshop.php' ?>


<!-- body -->     
<?php startblock('content') ?>



<div class="col-md-12" style="background-color: #f1f1f1; border-bottom: 1px solid #d1d1d1;">

    <div class="col-md-10">
        <h1>Xenopus </h1>
    </div>
    <div style="float: right; cursor: pointer;">
        <h1>   <span class="glyphicon glyphicon-shopping-cart my-cart-icon"><span class="badge badge-notify my-cart-badge"></span></span>
        </h1> 
    </div>
</div>


<div class="col-md-12">
    <div class="col-md-2 text-center" style="background-color: #f1f1f1; margin-top: 14px;">

        <p>CATEGORIES</p>

        <?php
        foreach ($categories as $category) {

            $selected = "";
            if ($id_category == $category['id']) {
                $selected = "active";
            }
            ?>
            <a class="btn btn-default btn-block <?php echo $selected ?>" href="estorecatalog/<?php echo $id_space ?>/<?php echo $category["id"] ?>" ><?php echo $category["name"] ?></a>
            <?php
        }
        ?>

    </div>
    <div class="col-md-10" style="background-color: #f1f1f1; min-height: 2000px; border-left: 1px solid #d1d1d1;">


        <div class="row">

            <?php
            foreach ($products as $product) {
                ?>
                <div class="col-md-3" style="background-color: #ffffff; border: 1px solid #e1e1e1; margin: 7px; padding: 7px;">
                    <div class="text-center">
                        <img src="<?php echo $product["url_image"] ?>" style="width: 100%;  max-width: 250px;" height="200px">
                    </div>
                    <br>
                    <div class="text-justified">
                        <b>
                            <?php echo $product["name"] ?>
                        </b>
                        <br>
                    </div>
                    <div class="text-justified">
                        <?php echo $product["description"] ?>
                    </div>
                    <br>
                    <div class="text-right">
                        <button class="btn btn-danger my-cart-btn" data-id="<?php echo $product["id"] ?>" data-name="<?php echo $product["name"] ?>" data-summary="<?php echo $product["name"] ?>" data-price="<?php echo $product["unit_quantity"] ?>" data-quantity="1" data-image="<?php echo $product["url_image"] ?>"><?php echo EstoreTranslator::AddToCart($lang) ?></button>
                    </div>    
                </div>
                <?php
            }
            ?>

        </div>
    </div>
</div>

<script src="https://netdna.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script type='text/javascript' src="externals/shoppingcart/js/jquery.mycart.js"></script>
<script type="text/javascript">
    $(function () {

        var goToCartIcon = function ($addTocartBtn) {
            var $cartIcon = $(".my-cart-icon");
            var $image = $('<img width="30px" height="30px" src="' + $addTocartBtn.data("image") + '"/>').css({"position": "fixed", "z-index": "999"});
            $addTocartBtn.prepend($image);
            var position = $cartIcon.position();
            $image.animate({
                top: position.top,
                left: position.left
            }, 500, "linear", function () {
                $image.remove();
            });
        }

        $('.my-cart-btn').myCart({
            currencySymbol: '',
            classCartIcon: 'my-cart-icon',
            classCartBadge: 'my-cart-badge',
            classProductQuantity: 'my-product-quantity',
            classProductRemove: 'my-product-remove',
            classCheckoutCart: 'my-cart-checkout',
            affixCartIcon: true,
            showCheckoutModal: true,
            numberOfDecimals: 0,
            checkoutCart: function (products) {

                //alert(JSON.stringify({"products": products}));

                $.post(
                        'essalenew/<?php echo $id_space ?>',
                        {products: products},
                        function (data) {
                            if (data.status === "success") {
                                $(location).attr('href', data.redirect);
                            } else {
                                alert(JSON.stringify(data.message));
                            }
                        },
                        'json'
                        );

            },
            clickOnAddToCart: function ($addTocart) {
                goToCartIcon($addTocart);
            }
        });

    });
</script>

<?php
endblock();



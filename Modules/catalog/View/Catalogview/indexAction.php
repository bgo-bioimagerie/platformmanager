<?php include 'Modules/core/View/layout.php' ?>



<?php startblock('content') ?>
<div class="container">
<?php include('Modules/catalog/View/Catalogview/toolbar.php') ?>


<div class="row" style="background-color:#ffffff;">
    <br/>
</div>
<div class="row my-gallery" style="background-color:#ffffff; min-height: 100%" itemscope itemtype="http://schema.org/ImageGallery">
    <?php foreach ($entries as $entry) {
        ?>

        <div class="col-12 col-md-4">
            <div class="card" style="text-align: center;">
                <div class="card-header"><?php echo $entry["title"] ?></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-4">
                        <?php
                        $imageFile = "data/catalog/" . $entry["image_url"];
                        if (file_exists($imageFile) && !is_dir($imageFile)) {
                            list($width, $height, $type, $attr) = getimagesize($imageFile);
                        ?>
                            <a href="<?php echo $imageFile ?>">
                                <img alt="prestation image" src="<?php echo $imageFile ?>" width="100%" />
                            </a>
                        <?php } ?>
                        </div>
                        <div class="col-8">
                            <div> <?php echo $entry["short_desc"] ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        
    <?php }
    ?>
</div>

</div>
<?php endblock(); ?>

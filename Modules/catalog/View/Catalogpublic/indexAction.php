<?php include 'Modules/catalog/View/publiclayout.php' ?>

<?php startblock('content') ?>

<?php include('Modules/catalog/View/Catalogpublic/toolbar.php') ?>

<div class="row" style="background-color:#ffffff;">
    <br/>
</div>
<div class="row my-gallery" style="background-color:#ffffff; min-height: 100%" itemscope itemtype="http://schema.org/ImageGallery">
    <?php foreach ($entries as $entry) {
        ?>
        <div class="col-md-8 col-md-offset-2">
            <div class="col-md-2">
                <?php
                $imageFile = "data/catalog/" . $entry["image_url"];
                if (!file_exists($imageFile) || is_dir($imageFile)) {
                   ?>
                    <div style="height: 70px;"></div>
                    <?php
                }
                else{
                    list($width, $height, $type, $attr) = getimagesize($imageFile);
                    ?>
                    <a href="<?php echo $imageFile ?>">
                        <img src="<?php echo $imageFile ?>" width="100%" />
                    </a>
                <?php 
                }
                ?>
            </div>
            <div class="col-md-10">
                <div style="font-weight: bold;"><?php echo $entry["title"] ?></div>
                <div> <?php echo $entry["short_desc"] ?></div>
            </div>	
        </div>
    <div class="col-md-12" style="height:7px;"></div>
    <?php }
    ?>
</div>



<?php endblock(); ?>
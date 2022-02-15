<?php include 'Modules/core/View/layout.php' ?>

<?php startblock('content') ?>

<?php include('Modules/catalog/View/Catalogview/toolbar.php') ?>

<div  style="background-color:#ffffff;">
    <br/>
</div>
<div class="my-gallery" style="background-color:#ffffff; min-height: 100%" itemscope itemtype="http://schema.org/ImageGallery">
    <?php foreach ($entries as $entry) {
        ?>
        <div class="col-12">
            <div class="col-2">
                <?php
                $imageFile = $entry["image"];
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
            <div class="col-10">
                <div style="font-weight: bold;"><?php echo $entry["name"] ?></div>
                <div><em> <?php echo $entry["category"] ?></em></div>
                <div> <?php echo $entry["description"] ?></div>
            </div>	
        </div>
    <div class="col-12" style="height:7px;"></div>
    <?php }
    ?>
</div>


<?php endblock(); ?>

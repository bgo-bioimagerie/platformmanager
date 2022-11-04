<?php include_once 'Modules/core/View/layout.php' ?>

<?php startblock('content') ?>
<div class="container">

<?php include('Modules/catalog/View/Catalogview/toolbar.php') ?>

<div class="row my-gallery" style="background-color:#ffffff; min-height: 100%" itemscope itemtype="http://schema.org/ImageGallery">
    <?php foreach ($entries as $entry) {
        ?>

        <div class="col-12 col-md-6">
            <div class="card" style="min-height: 200px; text-align: center;">
                <div class="card-header"><?php echo $entry["name"] ?> [<em><?php echo $entry["category"] ?></em>]</div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-12 col-md-2">
                        <?php
                        $imageFile = $entry["image"];
        if (!file_exists($imageFile) || is_dir($imageFile)) {
            ?>
                            
                            <?php
        } else {
            list($width, $height, $type, $attr) = getimagesize($imageFile);
            ?>
                            <a href="<?php echo $imageFile ?>">
                                <img alt="resource photo" src="<?php echo $imageFile ?>" width="100%" />
                            </a>
                        <?php
        }
        ?>
                        </div>
                        <div class="col-12 col-md-4">
                            <div> <?php echo $entry["description"] ?></div>
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

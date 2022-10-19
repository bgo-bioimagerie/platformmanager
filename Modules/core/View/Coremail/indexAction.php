<?php
include_once 'Modules/core/View/spacelayout.php';
require_once 'Modules/core/Model/CoreTranslator.php';
?>

    
<?php startblock('content') ?>
<div >
    <h3>Mail subscriptions</h3>
    <div class="container">
    <form action="/coremail/<?php echo $idSpace; ?>" method="POST">
    <div class="bm-3 row">
    <?php foreach ($mods as $key => $mod) { ?>
        <div class="col-4 form-check">
            <label class="form-check-label" for="<?php echo $key; ?>"><?php echo $key; ?></label>
            <input class="form-check-input" type="checkbox" <?php if ($mod) {
                echo "checked";
            }?> id="<?php echo $key; ?>" name="s_<?php echo $key; ?>"/>
        </div>
    <?php } ?>
    </div>
    <div><button style="margin-top: 10px" type="submit" class="btn btn-primary"><?php echo CoreTranslator::Update($lang); ?></button></div>
    </form>
    </div>

</div>
<?php endblock(); ?>
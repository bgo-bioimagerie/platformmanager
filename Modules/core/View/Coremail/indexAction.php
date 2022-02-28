<?php
include 'Modules/core/View/spacelayout.php';
require_once 'Modules/core/Model/CoreTranslator.php';
?>

    
<?php startblock('content') ?>
<div >
    <h3>Mail subscriptions</h3>
    <div class="container">
    <form action="/coremail/<?php echo $id_space; ?>" method="POST">
    <div class="row">
    <?php foreach($mods as $key => $mod) { ?>
        <div class="form-group col-sm-2 cl-md-2">
        <input class="form-check-input" type="checkbox" <?php if($mod) { echo "checked"; }?> id="<?php echo $key; ?>" name="s_<?php echo $key; ?>"/>
        <label class="form-check-label" for="<?php echo $key; ?>"><?php echo $key; ?></label>
        </div>
    <?php } ?>
    </div>
    <div><button type="submit" class="btn btn-primary"><?php echo CoreTranslator::Update($lang); ?></button></div>
    </form>
    </div>

</div>
<?php endblock(); ?>
<?php
include 'Modules/core/View/spacelayout.php';
require_once 'Modules/core/Model/CoreTranslator.php';
?>

<!-- body -->     
<?php startblock('content') ?>
<h3>Mail subscriptions</h3>
<div class="row container">
<form action="/coremail/<?php echo $id_space; ?>" method="POST">
<?php foreach($mods as $key => $mod) { ?>
<div class="form-group col-sm-6 cl-md-2">
<label for="<?php echo $key; ?>"><?php echo $key; ?></label>
<input type="checkbox" <?php if($mod) { echo "checked"; }?> id="<?php echo $key; ?>" name="s_<?php echo $key; ?>"/>
</div>
<?php } ?>
</div>
<button type="submit" class="btn btn-primary"><?php echo CoreTranslator::Update($lang); ?></button>
</form>
<?php endblock();
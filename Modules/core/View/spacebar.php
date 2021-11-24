<?php
require_once 'Modules/core/Controller/CorespaceController.php';
?>
<div class="col-md-12" style="background-color: #428bca;">
    <div class="col-md-2">
        <div class="dropdown">
            <button id="dLabel" type="button" class="btn  btn-primary" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <?php echo CoreTranslator::Tools($lang) ?>
            </button>
            <div class="dropdown-menu col-md-2" aria-labelledby="dLabel">
                <?php
                require_once 'Modules/core/Controller/CorespaceController.php';
                $spaceMenu = new CorespaceController(new Request(array(), false));
                ?>
                <?php echo $spaceMenu->menu($id_space); ?>
            </div>
        </div>
    </div>
    <div class="col-md-8" style="text-align: center;">

        <p style="color:#fff;">
            <strong><?php echo $spaceMenu->spaceName($id_space); ?></strong>
        </p>

    </div>
</div>
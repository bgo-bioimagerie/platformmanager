<?php include 'Modules/resources/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="col-md-12" id="pm-content">
    <div class="col-md-12" id="pm-form">

        <?php include "Modules/resources/View/Resources/edittabs.php" ?>
        <div class="col-xs-12"><p></p></div>
                <?php echo $formHtml ?>
    </div>
</div>
<?php
endblock();

<?php include 'Modules/resources/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
    <div class="pm-form">

        <?php include "Modules/resources/View/Resourcesinfo/edittabs.php" ?>
        <div class="col-xs-10"><p></p></div>
                <?php echo $formHtml ?>
    </div>
<?php
endblock();

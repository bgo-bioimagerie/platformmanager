<?php include 'Modules/resources/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>
<div class="pm-form">
    <?php include "Modules/resources/View/Resourcesinfo/edittabs.php" ?>
    <div><p></p></div>
    <?php echo $formHtml ?>
</div>
<?php
endblock();

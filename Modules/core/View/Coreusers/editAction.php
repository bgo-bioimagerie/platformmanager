<?php include 'Modules/core/View/layout.php' ?>


<!-- body -->     
<?php startblock('content') ?>
<div class="container">
    <?php echo $formHtml ?>
    
    <?php echo $formPwdHtml ?>
</div> <!-- /container -->
<?php echo $script ?>
<?php
endblock();

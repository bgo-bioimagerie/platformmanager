<?php include_once 'Modules/core/View/layout.php' ?>


    
<?php startblock('content') ?>
<div class="container">
    <?php echo $formHtml ?>
    
    <?php echo $formPwdHtml ?>

    <?php echo $rolesTableHtml ?>
</div> <!-- /container -->
<?php echo $script ?>
<?php endblock(); ?>

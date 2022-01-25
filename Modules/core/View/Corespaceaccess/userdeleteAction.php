<?php include 'Modules/core/View/Corespaceaccess/layout.php' ?>

    
<?php startblock('content') ?>

<div class="row pm-table"> 

    <?php if (isset($_SESSION["message"]) && $_SESSION["message"]) { ?>

        <div class="alert alert-danger">
            <?php echo $_SESSION["message"] ?>
        </div>

    <?php 
    unset($_SESSION["message"]);
    } ?>

</div>

<?php endblock(); ?>

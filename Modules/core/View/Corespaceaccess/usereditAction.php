<?php include 'Modules/core/View/Corespaceaccess/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="row pm-table"> 

    <?php if (isset($_SESSION["message"]) && $_SESSION["message"]) { ?>

        <div class="alert alert-success">
            <?php echo $_SESSION["message"] ?>
        </div>

    <?php 
    unset($_SESSION["message"]);
    } ?>

    <?php echo $formHtml ?>
    <?php echo "PHP" ?>

</div>

<?php
endblock();

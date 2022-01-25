<?php include 'Modules/resources/View/layout.php' ?>

    
<?php startblock('content') ?>

<div>  
    <?php if (isset($_SESSION["message"]) && $_SESSION["message"] != "") { ?>
        <div class="alert alert-danger alert-dismissible">
            <?php echo $_SESSION["message"] ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    <?php 
    unset($_SESSION["message"]);
    } ?>
</div>
<div class="pm-table">
    <?php echo $tableHtml ?>
</div>
<?php endblock(); ?>

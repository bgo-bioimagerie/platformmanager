<?php include 'Modules/core/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-12 pm-nav">
    <?php include('Modules/core/View/Coremainmenu/navbar.php'); ?>
</div>    

<div class="col-md-12 pm-top-double-bar">
    <div class="container pm-table">
        <?php 
        if (isset($_SESSION["message"]) && $_SESSION["message"] != ""){ 
            ?>
            <div class="col-xs-12 col-md-12" style="padding-top: 12px;" >
                <div class="alert alert-success" role="alert">
                <p><?php echo $_SESSION["message"] ?></p>
                </div>
            </div>
        <?php }
        $_SESSION["message"] = "";
        ?>

        <a class="btn btn-default" href="coremainmenuitemedit/0"><?php echo CoreTranslator::NewItem($lang) ?></a>
        <?php echo $tableHtml ?>
    </div>
</div>
<?php endblock();
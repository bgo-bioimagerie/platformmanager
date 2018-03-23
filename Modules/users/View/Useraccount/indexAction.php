<?php include 'Modules/core/View/layout.php' ?>


<!-- body -->     
<?php startblock('content') ?>

<?php include( "Modules/core/View/Coreusers/navbar.php" ); ?>

<div class="col-md-12" style="margin-top:50px;">
    
    
    <div class="container pm-form">
        
            <div class="col-sm-10 col-sm-offset-1 text-center">
             <?php
        if (isset($_SESSION["message"])) {
            if (substr($_SESSION["message"], 0, 3) === "Err") {
                ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION["message"] ?>
                </div>
                <?php
            } else {
                ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION["message"] ?>
                </div>
                <?php
            }
            unset($_SESSION["message"]);
        }
        ?>
        </div>
        
        <?php echo $formHtml ?>
    </div>
</div> <!-- /container -->
<?php
endblock();
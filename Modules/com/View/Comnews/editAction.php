<?php include 'Modules/documents/View/layout.php' ?>

<!-- body --> 
<?php startblock('content') ?>

<div class="col-md-12 pm-form" >
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
    <?php echo $formHtml ?>
</div>
<?php
endblock();

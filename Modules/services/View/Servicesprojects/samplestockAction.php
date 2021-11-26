<?php include 'Modules/services/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="pm-form">

    <div class="col-md-12">
        <h3> <?php echo $projectName ?> </h3>
    </div>

    <?php include 'Modules/services/View/Servicesprojects/projecttabs.php'; ?>

    <div class="col-md-12">
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

<?php
endblock();

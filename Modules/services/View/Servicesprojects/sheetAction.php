<?php include 'Modules/services/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-12 pm-form">

    <div class="col-md-12">
    <h3> <?php echo $projectName ?> </h3>
    </div>
    
    <?php include 'Modules/services/View/Servicesprojects/projecttabs.php'; ?>

    <?php
    if (isset($_SESSION["message"])) {
        ?>
        <div class="alert alert-success">
            <?php echo $_SESSION["message"] ?>
        </div>
        <?php
        unset($_SESSION["message"]);
    }
    ?>

    <?php echo $formHtml ?>
</div>

<?php
endblock();

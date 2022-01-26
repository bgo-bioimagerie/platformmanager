<?php include 'Modules/services/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-form">

    <div class="col-md-12">
        <h3> <?php echo $projectName ?> </h3>
    </div>

    <div class="col-md-12">
        <?php include 'Modules/services/View/Servicesprojects/projecttabs.php'; ?>
    </div>

    <?php echo $formHtml ?>
</div>

<?php endblock(); ?>

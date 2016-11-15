<?php include 'Modules/database/View/configlayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-10 col-xs-12" id="pm-form">
    <?php include 'Modules/database/View/configmenu.php'; ?> 
    
    <div class="col-md-10 col-xs-12" id="pm-form">
    <?php echo $formEdit ?>
    <?php echo $formAttributs ?>
    </div>
    <?php include 'Modules/database/View/Databaseconfig/viewsmenu.php'; ?>
</div>

<?php endblock();
<?php include 'Modules/bulletjournal/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="col-md-10" id="pm-content">
    
    <?php include "Modules/bulletjournal/View/Bjmigrations/indexHeader.php" ?>
    
    <div class="col-md-6 col-12" id="pm-form" style="margin-right:5px;">
        <?php include "Modules/bulletjournal/View/Bjmigrations/indexTasks.php" ?>
    </div>
</div>


<!--  ************  -->
<!--   Javascript   -->
<!--  ************  -->
<?php include "Modules/bulletjournal/View/Bjmigrations/indexJS.php"; ?>

<?php endblock(); ?>

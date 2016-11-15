<?php include 'Modules/database/View/configlayout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="col-md-10 col-xs-12" id="pm-form">
    <?php include 'Modules/database/View/configmenu.php'; ?> 
    
    <div class="col-md-12 col-xs-12" id="pm-form">
        
    <?php for($i = 0 ; $i < count($forms) ; $i++){
        echo $forms[$i]->getHtml($lang);
    }
    ?>
    </div>
</div>

<?php endblock();
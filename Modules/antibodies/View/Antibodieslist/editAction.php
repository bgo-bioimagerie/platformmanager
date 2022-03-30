<?php include 'Modules/antibodies/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-content">
    
    <div class="col-12 pm-form-short">
        <?php echo $form ?>
        <a class="btn btn-danger" href="antibodydelete/<?php echo $id_space ?>/<?php echo $id ?>"><?php echo CoreTranslator::Delete($lang) ?></a>
    </div>
    <div class="col-12">
        <div class="col-12 pm-table-short">
            <?php echo $tissusTable ?>
            <a class="btn btn-primary" id="addtissusbutton"><?php echo AntibodiesTranslator::addTissus($lang) ?></a>
        </div>
        
        <div class="col-12 pm-table-short">
            <?php echo $formCatalog ?>
        </div>
        
        <div class="col-12 pm-table-short">
            <?php echo $ownersTable ?>
            <a class="btn btn-primary" id="addownerbutton"><?php echo AntibodiesTranslator::addOwner($lang) ?></a>
        </div>
    </div>
    
</div>

<!--  *************  -->
<!--  Popup windows  -->
<!--  *************  -->
<link rel="stylesheet" type="text/css" href="Framework/pm_popup.css">
<div id="hider" class="col-12"></div> 
<div id="tissuspopup_box" class="pm_popup_box" style="display: none;">
    <div class="col-1 offset-11" style="text-align: right;"><a id="tissusbuttonclose" class="bi-x-circle-fill" style="cursor:pointer;"></a></div>
        <?php echo $formtissus ?>
</div> 
<div id="ownerpopup_box" class="pm_popup_box" style="display: none;">
    <div class="col-1 offset-11" style="text-align: right;"><a id="ownerbuttonclose" class="bi-x-circle-fill" style="cursor:pointer;"></a></div>
        <?php echo $formowner ?>
</div> 

<?php include 'Modules/antibodies/View/Antibodieslist/editscript.php';  ?>

<?php endblock(); ?>
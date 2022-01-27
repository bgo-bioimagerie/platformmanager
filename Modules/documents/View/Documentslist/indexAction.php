<?php include 'Modules/documents/View/layout.php' ?>


<?php startblock('content') ?>
<div class="pm-table"> 

    <?php if($userSpaceStatus){ ?> 
    <div class="col-md-2" style="padding-top:7px;">
        <button type="button" class="btn btn-outline-dark" onclick="window.location.href = 'documentsedit/<?php echo $id_space ?>/0/'">
            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo DocumentsTranslator::Add_Doc($lang) ?>
        </button>
        <p></p>
    </div>
    <?php } ?>
    <div class="col-md-12" >
        <?php echo $tableHtml ?>
    </div>
</div>

<?php endblock(); ?>
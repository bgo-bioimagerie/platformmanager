<?php include 'Modules/core/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="container pm-form">
    <div class="row">
    <div class="col-2 col-lg-offset-10" style="padding-top:7px;">
        <button type="button" class="btn btn-outline-dark" onclick="window.location.href = 'spaceadminedit/0'">
            <span class="bi-plus" aria-hidden="true"></span> <?php echo CoreTranslator::Add_Space($lang) ?>
        </button>
        <p></p>
    </div>
    <div class="col-12" >
        <?php echo $tableHtml ?>
    </div>
    </div>
</div>
<?php endblock(); ?>

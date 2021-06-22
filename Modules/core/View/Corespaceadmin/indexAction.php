<?php include 'Modules/core/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<div class="container pm-form">
    <div class="col-md-2 col-lg-offset-10" style="padding-top:7px;">
        <button type="button" class="btn btn-default" onclick="window.location.href = 'spaceadminedit/0'">
            <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> <?php echo CoreTranslator::Add_Space($lang) ?>
        </button>
        <p></p>
    </div>
    <div class="col-md-12" >
        <?php echo $tableHtml ?>
    </div>
</div>
<?php
endblock();

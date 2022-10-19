<?php include_once 'Modules/bulletjournal/View/layout.php' ?>

    
<?php startblock('content') ?>
<div class="row">
<div class="col-10" id="pm-content">
    
    <?php include "Modules/bulletjournal/View/Bjnotes/indexHeader.php" ?>
    
    <div class="col-6 col-12" id="pm-form" style="margin-right:5px;">
        <?php include "Modules/bulletjournal/View/Bjnotes/indexDays.php" ?>
    </div>
    <div class="col-5 col-12" id="pm-form" style="margin-left:0px;">
        <?php include "Modules/bulletjournal/View/Bjnotes/indexMonth.php" ?>
    </div>
</div>
</div>

<!--  *************  -->
<!--  Popup windows  -->
<!--  *************  -->

<div id="notepopup_box" class="modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo BulletjournalTranslator::Notes($lang) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
        <?php echo $noteForm ?>
        </div>
        </div>
    </div>
</div>

<div id="taskpopup_box" class="modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo BulletjournalTranslator::Task($lang) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
        <?php echo $taskForm ?>
        </div>
        </div>
    </div>
</div>

<div id="eventpopup_box" class="modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo BulletjournalTranslator::Event($lang) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
        <?php echo $eventForm ?>
        </div>
        </div>
    </div>
</div>


<!--  ************  -->
<!--   Javascript   -->
<!--  ************  -->
<?php include "Modules/bulletjournal/View/Bjnotes/indexJS.php"; ?>

<?php endblock(); ?>

<?php include 'Modules/bulletjournal/View/layout.php' ?>

    
<?php startblock('content') ?>
<div class="row">
<div class="col-md-10" id="pm-content">
    
    <?php include "Modules/bulletjournal/View/Bjnotes/indexHeader.php" ?>
    
    <div class="col-md-6 col-xs-12" id="pm-form" style="margin-right:5px;">
        <?php include "Modules/bulletjournal/View/Bjnotes/indexDays.php" ?>
    </div>
    <div class="col-md-5 col-xs-12" id="pm-form" style="margin-left:0px;">
        <?php include "Modules/bulletjournal/View/Bjnotes/indexMonth.php" ?>
    </div>
</div>
</div>

<!--  *************  -->
<!--  Popup windows  -->
<!--  *************  -->
<link rel="stylesheet" type="text/css" href="Framework/pm_popup.css">
<div id="hider" class="col-xs-12"></div> 
<!--  note edit popup  -->
<div id="notepopup_box" class="pm_popup_box" style="display: none;">
    <div class="col-md-1 col-md-offset-11" style="text-align: right;"><a id="notebuttonclose" class="bi-x-circle-fill" style="cursor:pointer;"></a></div>
    <?php echo $noteForm ?>
</div> 
<div id="taskpopup_box" class="pm_popup_box" style="display: none;">
    <div class="col-md-1 col-md-offset-11" style="text-align: right;"><a id="taskbuttonclose" class="bi-x-circle-fill" style="cursor:pointer;"></a></div>
    <?php echo $taskForm ?>
</div> 
<div id="eventpopup_box" class="pm_popup_box" style="display: none;">
    <div class="col-md-1 col-md-offset-11" style="text-align: right;"><a id="eventbuttonclose" class="bi-x-circle-fill" style="cursor:pointer;"></a></div>
        <?php echo $eventForm ?>
</div> 
<div id="collectionspopup_box" class="pm_popup_box" style="display: none;">
    <div class="col-md-1 col-md-offset-11" style="text-align: right;"><a id="collectionsbuttonclose" class="bi-x-circle-fill" style="cursor:pointer;"></a></div>
        <?php echo $collectionsForm ?>
</div> 


<!--  ************  -->
<!--   Javascript   -->
<!--  ************  -->
<?php include "Modules/bulletjournal/View/Bjnotes/indexJS.php"; ?>

<?php endblock(); ?>

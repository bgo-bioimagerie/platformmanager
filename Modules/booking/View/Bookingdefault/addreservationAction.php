<?php include 'Modules/booking/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="pm-form">

    <?php echo $form->htmlOpen() ?>
    <?php echo $form->getHtml($lang, false) ?>
    <script type="module">
        import {DynamicForms} from '/externals/pfm/dynamics/dynamicForms.js';
        let dynamicForms = new DynamicForms();
        let spaceId = <?php echo $id_space?>;
        let sourceId = "recipient_id";
        let targets = [
            {
                elementId: "responsible_id",
                apiRoute: `clientusers/getclients/`
            }
        ];
        dynamicForms.dynamicFields(sourceId, targets, spaceId);
    </script>

    <?php
    $checked = "";
    if ($packageChecked) {
        $checked = "checked";
    }
    ?>

    <?php if ($use_packages) { ?>
        <div>
            <div class="checkbox col-xs-8 col-xs-offset-4">
                <label>
                    <input id="use_package" type="checkbox" name="use_package" value="yes" <?php echo $checked ?> > <?php echo BookingTranslator::Use_Package($lang) ?>
                </label>
            </div>

            <div id="package_div">
                <?php echo $formPackage ?>
            </div>
        </div>
    <?php } ?>
    <div>
        <div id="resa_time_div">
            <?php echo $formEndDate ?>
        </div>
    </div>

    <!-- Periodicity -->
    <?php if ($usePeriodicBooking) {
        ?>            
        <div class="form-group">
            <label class="control-label col-xs-4">
                <?php echo BookingTranslator::PeriodicityType($lang) ?> 
            </label>
            <div class="col-xs-6">
                <div class="radio">
                    <label><input  type="radio" name="periodic_radio" value="1" <?php if($periodInfo['choice'] == 1){echo 'checked="checked"';} ?>><?php echo BookingTranslator::None($lang) ?></label>
                </div>
                <div class="radio">
                    <label><input  type="radio" name="periodic_radio" value="2" <?php if($periodInfo['choice'] == 2){echo 'checked="checked"';} ?>><?php echo BookingTranslator::EveryDay($lang) ?></label>
                </div>
                <div class="radio">
                    <label><input  type="radio" name="periodic_radio" value="3" <?php if($periodInfo['choice'] == 3){echo 'checked="checked"';} ?>></label>
                    <select class="form-control" name="periodic_week">
                        <option value="1"><?php echo BookingTranslator::EveryWeek($lang) ?></option>
                        <option value="2"><?php echo BookingTranslator::Every2Week($lang) ?></option>
                        <option value="3"><?php echo BookingTranslator::Every3Week($lang) ?></option>
                        <option value="4"><?php echo BookingTranslator::Every4Week($lang) ?></option>
                        <option value="5"><?php echo BookingTranslator::Every5Week($lang) ?></option>
                    </select>
                </div>
                <div class="radio">
                    <label ><input type="radio" name="periodic_radio" value="4"></label>
                    <select class="form-control" name="periodic_month">
                        <option value="1"><?php echo BookingTranslator::EveryMonthSameDate($lang) ?></option>
                        <option value="2"><?php echo BookingTranslator::EveryMonthSameDay($lang) ?></option>
                    </select>
                </div>
                <div class="radio">
                    <label><input type="radio" name="periodic_radio" value="5"><?php echo BookingTranslator::EveryYearSameDate($lang) ?></label>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label col-xs-4">
                <?php echo BookingTranslator::DateEndPeriodicity($lang) ?> 
            </label>
            <div class="col-xs-6">
                <div class='col-xs-12 input-group date'>
                    <input type='date' class="form-control" id="resa_start" name="periodic_enddate" value="<?php echo $periodInfo['enddate'] ?>"/>          
                    <span class="input-group-addon">          
                        <span class="glyphicon glyphicon-calendar"></span>          
                    </span>
                </div>

            </div>
        </div>
    </div>    

    <?php } ?>
<!-- End periodicity -->

<div class="col-xs-12"></div>
<div id="buttons" class="col-xs-4 col-xs-offset-8">
    <?php if ($userCanEdit) { ?>	
        <input type="submit" class="btn btn-primary" value="Save" />
        <?php if ($id_reservation > 0) { ?>
            <button id="deletebookingbutton" type="button" class="btn btn-danger"><?php echo CoreTranslator::Delete($lang) ?></button>
        <?php
            if ($id_period > 0){
                ?>
                <button id="deletebookingperiodbutton" type="button" class="btn btn-danger"><?php echo BookingTranslator::DeletePeriod($lang) ?></button>
            <?php 
            }
        }
    }
    ?>
    <?php
        $q = '?';
        $redirPage = '';
        if($from) {
            $redirInfo = explode(':', $from);
            $redirPage = $redirInfo[0];
            $q = "bk_curentDate=$redirInfo[1]&bk_id_resource=$redirInfo[2]&bk_id_area=$redirInfo[3]&id_user=$redirInfo[4]";
        }
        $url = "booking$redirPage/$id_space?$q"
    ?>
    <button type="button" class="btn btn-default" onclick="location.href = '<?php echo $url ?>'"><?php echo CoreTranslator::Cancel($lang) ?></button>
</div>

<?php echo $form->htmlClose() ?>

</div>


<?php
$isPackageCheched = 0;
if ($packageChecked > 0) {
    $isPackageCheched = 1;
}
?>

<script>
    $(document).ready(function () {
        let php_var = "<?php echo $isPackageCheched; ?>";
        if (php_var === "1") {
            document.getElementById('resa_time_div').style.display = 'none';
        } else {
            let p_div = document.getElementById('package_div');
            if (p_div) { p_div.style.display = 'none'; }
        }

        let use_package = document.getElementById('use_package');
        if (use_package) {
            document.getElementById('use_package').onchange = function () {
                let p_div = document.getElementById('package_div');
                if(p_div) { p_div.style.display = this.checked ? 'block' : 'none'; }
                document.getElementById('resa_time_div').style.display = !this.checked ? 'block' : 'none';
            }
        }
        ;
    });

</script>

<!--  *************  -->
<!--  Popup windows  -->
<!--  *************  -->
<link rel="stylesheet" type="text/css" href="Framework/pm_popup.css">
<div id="hider" class="col-xs-12"></div> 
<div id="entriespopup_box" class="pm_popup_box" style="display: none;">
    <div class="col-md-1 col-md-offset-11" style="text-align: right;"><a id="entriesbuttonclose" class="glyphicon glyphicon-remove" style="cursor:pointer;"></a>
</div>
<?php echo $formDelete ?>
</div> 

<div id="entriesperiodpopup_box" class="pm_popup_box" style="display: none;">
    <div class="col-md-1 col-md-offset-11" style="text-align: right;"><a id="entriesperiodbuttonclose" class="glyphicon glyphicon-remove" style="cursor:pointer;"></a>
</div>
<?php echo $formDeletePeriod ?>
</div> 

<?php include 'Modules/booking/View/Bookingdefault/deletescript.php'; ?>

<?php endblock(); ?>

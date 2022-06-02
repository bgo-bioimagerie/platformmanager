<?php include 'Modules/booking/View/layout.php' ?>

    
<?php startblock('content') ?>

<div class="container pm-form">
    <div class="row mb-3">
        <div class="col-12">
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
                <div class="row mb-3">
                    <div class="checkbox col-8 mb-3">
                        <label>
                            <input id="use_package" <?php if($forcePackages) { echo "disabled";}  ?> class="form-checkbox" type="checkbox" name="use_package" value="yes" <?php echo $checked ?> > <?php echo BookingTranslator::Use_Package($lang) ?>
                        </label>
                    </div>

                    <div class="mb-3" id="package_div">
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
                <div class="row mb-3">
                    <label class="control-label col-4">
                        <?php echo BookingTranslator::PeriodicityType($lang) ?> 
                    </label>
                    <div class="col-6">
                        <div class="form-check mb-1">
                            <label class="form-check-label"><input class="form-check-input" type="radio" name="periodic_radio" value="1" <?php if($periodInfo['choice'] == 1){echo 'checked="checked"';} ?>><?php echo BookingTranslator::None($lang) ?></label>
                        </div>
                        <div class="form-check mb-1">
                            <label class="form-check-label"><input class="form-check-input" type="radio" name="periodic_radio" value="2" <?php if($periodInfo['choice'] == 2){echo 'checked="checked"';} ?>><?php echo BookingTranslator::EveryDay($lang) ?></label>
                        </div>
                        <div class="form-check mb-1">
                            <label class="form-check-label"><input class="form-check-input" type="radio" name="periodic_radio" value="3" <?php if($periodInfo['choice'] == 3){echo 'checked="checked"';} ?>></label>
                            <select class="form-control" name="periodic_week">
                                <option value="1"><?php echo BookingTranslator::EveryWeek($lang) ?></option>
                                <option value="2"><?php echo BookingTranslator::Every2Week($lang) ?></option>
                                <option value="3"><?php echo BookingTranslator::Every3Week($lang) ?></option>
                                <option value="4"><?php echo BookingTranslator::Every4Week($lang) ?></option>
                                <option value="5"><?php echo BookingTranslator::Every5Week($lang) ?></option>
                            </select>
                        </div>
                        <div class="form-check mb-1">
                            <label class="form-check-label"><input class="form-check-input" type="radio" name="periodic_radio" value="4"></label>
                            <select class="form-control" name="periodic_month">
                                <option value="1"><?php echo BookingTranslator::EveryMonthSameDate($lang) ?></option>
                                <option value="2"><?php echo BookingTranslator::EveryMonthSameDay($lang) ?></option>
                            </select>
                        </div>
                        <div class="form-check mb-1">
                            <label class="form-check-label"><input class="form-check-input" type="radio" name="periodic_radio" value="5"><?php echo BookingTranslator::EveryYearSameDate($lang) ?></label>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="control-label col-4">
                        <?php echo BookingTranslator::DateEndPeriodicity($lang) ?> 
                    </label>
                    <div class="col-6">
                        <div class='input-group date'>
                            <input type='date' class="form-control" id="resa_start" name="periodic_enddate" value="<?php echo $periodInfo['enddate'] ?>"/>          
                        </div>
                    </div>
                </div>
            <?php } ?>
            <!-- End periodicity -->
        </div>  

        <div class="col-12">
            <div id="buttons" class="col-4 offset-8">
                <?php if ($userCanEdit) { ?>	
                    <input type="submit" class="btn btn-primary" value="Save" />
                    <?php if ($id_reservation > 0) { ?>
                        <button onclick="showDelete()" id="deletebookingbutton" type="button" class="btn btn-danger"><?php echo CoreTranslator::Delete($lang) ?></button>
                    <?php
                        if ($id_period > 0){
                            ?>
                            <button onclick="showDeletePeriod()" id="deletebookingperiodbutton" type="button" class="btn btn-danger"><?php echo BookingTranslator::DeletePeriod($lang) ?></button>
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
                    $q = "bk_curentDate=$redirInfo[1]&bk_id_resource=$redirInfo[2]&bk_id_area=$redirInfo[3]&id_user=$redirInfo[4]&view=$redirInfo[5]";
                }
                $url = "booking$redirPage/$id_space?$q"
                ?>
                <button type="button" class="btn btn-outline-dark" onclick="location.href = '<?php echo $url ?>'"><?php echo CoreTranslator::Cancel($lang) ?></button>
            </div>

            <?php echo $form->htmlClose() ?>
        </div>
    </div>

    <?php if($details['steps']) { ?>
    <div class="row">
        <div class="col-xs-12">
            <table aria-label="details of reservation" class="table">
                <thead><tr><th scope="col" aria-label="day/night/weekend/closed">Type</th><th scope="col">Date</th><th scope="col"><?php echo BookingTranslator::Duration($lang) ?> (h)</th></tr></thead>
                <tbody>
                <?php foreach ($details['steps'] as $step) { ?>
                    <tr>
                        <td><?php 
                        switch ($step['kind']) {
                            case 'day':
                                $color = 'yellow';
                                $txtcolor = 'black';
                                break;
                            case 'night':
                                $color = 'black';
                                $txtcolor = 'white';
                                break;
                            case 'we':
                                $color = 'orange';
                                $txtcolor = 'black';
                                break;
                            case 'closed':
                                $color = 'red';
                                $txtcolor = 'black';
                                break;
                            default:
                                $color = 'blue';
                                $txtcolor = 'white';
                                break;
                        }
                        echo '<span class="label" style="background-color: '.$color.';color: '.$txtcolor.'">'.$step['kind'].'</label>';
                        ?></td>
                        <td><?php echo date('Y-m-d H:i', $step['start']).' => '.date('Y-m-d H:i', $step['end']) ?></td>
                        <td><?php echo $step['duration']/3600 ?>h</td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php } ?>
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
<div id="entriespopup_box" class="modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo CoreTranslator::Delete($lang) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <?php echo $formDelete ?>
        </div>
        </div>
    </div>
</div>
<div id="entriesperiodpopup_box" class="modal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title"><?php echo BookingTranslator::DeletePeriod($lang) ?></h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
            <?php echo $formDeletePeriod ?>
        </div>
        </div>
    </div>
</div>

<script>
    function showDelete() {
        let myModal = new bootstrap.Modal(document.getElementById('entriespopup_box'))
        myModal.show();
    }

    function showDeletePeriod() {
        let myModal = new bootstrap.Modal(document.getElementById('entriesperiodpopup_box'))
        myModal.show();
    }

</script>

<?php endblock(); ?>

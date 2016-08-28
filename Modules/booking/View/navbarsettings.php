<?php
require_once 'Modules/core/Model/CoreConfig.php';
$modelCoreConfig = new CoreConfig();
$menucolor = $modelCoreConfig->getParamSpace("bookingsettingsmenucolor", $id_space);
$menucolortxt = $modelCoreConfig->getParamSpace("bookingsettingsmenucolortxt", $id_space);
if ($menucolor == "") {
    $menucolor = "#337ab7";
}
if ($menucolortxt == "") {
    $menucolortxt = "#ffffff";
}
?>

<div class="col-md-12" style="padding: 7px; background-color: <?php echo $menucolor ?>; color:<?php echo $menucolortxt ?>;">

    <div class="col-md-2" style="margin-top: -10px;">
        <h2><?php echo BookingTranslator::Booking_settings($lang) ?></h2>
    </div>
    <div class="col-md-10">
        <div class="col-md-4">
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingscheduling/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Scheduling($lang) ?></button>
            </div>
            <br/>
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingdisplay/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Display($lang) ?></button>
            </div>
            <br/>
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingaccessibilities/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Accessibilities($lang) ?></button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingsupsinfo/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::SupplementariesInfo($lang) ?></button>
            </div>
            <br/>
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingpackages/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Packages($lang) ?> </button>
            </div>
            <br/>
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingquantities/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Quantities($lang) ?></button>
            </div>
        </div>


        <div class="col-md-4">
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingnightwe/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Nightwe($lang) ?></button>
            </div>
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingcolorcodes/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Color_codes($lang) ?></button>
                <button onclick="location.href = 'bookingcolorcodeedit/<?php echo $id_space ?>/0'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;">+</button>
            </div>
            <br/>
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingblock/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Block_Resouces($lang) ?></button>
            </div>		
        </div>

    </div>

</div>

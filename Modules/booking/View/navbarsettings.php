<?php
require_once 'Modules/core/Model/CoreConfig.php';
$modelCoreConfig = new CoreConfig();
$menucolor = $modelCoreConfig->getParam("bookingsettingsmenucolor");
$menucolortxt = $modelCoreConfig->getParam("bookingsettingsmenucolortxt");
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
                <button onclick="location.href = 'bookingscheduling/'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Scheduling($lang) ?></button>
            </div>
            <br/>
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingdisplay/'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Display($lang) ?></button>
            </div>
            <br/>
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingaccessibilities/'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Accessibilities($lang) ?></button>
            </div>
        </div>
        <div class="col-md-4">
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingsupsinfo/'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::SupplementariesInfo($lang) ?></button>
            </div>
            <br/>
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingpackages'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Packages($lang) ?> </button>
            </div>
            <br/>
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingquantities/'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Quantities($lang) ?></button>
            </div>
        </div>


        <div class="col-md-4">
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingcolorcodes/'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Color_codes($lang) ?></button>
                <button onclick="location.href = 'bookingcolorcodeedit/0'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;">+</button>
            </div>
            <br/>
            <div class="btn-group col-xs-12" data-toggle="buttons">
                <button onclick="location.href = 'bookingblock/'" class="btn btn-link" style="color: <?php echo $menucolortxt ?>;"><?php echo BookingTranslator::Block_Resouces($lang) ?></button>
            </div>		
        </div>

    </div>

</div>

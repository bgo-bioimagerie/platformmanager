<?php
require_once 'Modules/core/Model/CoreConfig.php';
$modelCoreConfig = new CoreConfig();
$ecmenucolor = ""; //$modelCoreConfig->getParamSpace("ecosystemmenucolor", $id_space);
$ecmenucolortxt = ""; //$modelCoreConfig->getParamSpace("ecosystemmenucolortxt", $id_space);
if ($ecmenucolor == "") {
    $ecmenucolor = "#f1f1f1";
}
if ($ecmenucolortxt == "") {
    $ecmenucolortxt = "#000";
}
?>

<head>
    <style>
        #menu-button-div a{
            font: 12px Arial;
            text-decoration: none;
            color: #333333;
            padding-left: 12px;
            /* padding: 2px 6px 2px 6px; */
        }

        #menu-button-div{
            margin-top: -2px;
            /* padding: 2px 6px 2px 6px; */
        }

        #menu-button-div:hover{
            font: 12px Arial;
            text-decoration: none;
            background-color: #e1e1e1;
            color: #333333;
            padding: 2px 2px 2px 2px;
        }

        #separatorp{
            padding-top: 12px;
            text-transform: uppercase; 
            font-weight: bold; 
            font-size: 11px;
            color: #616161;
        }
    </style>
</head>

<div class="col-md-2" style="padding: 7px; background-color: <?php echo $ecmenucolor ?>; color:<?php echo $ecmenucolortxt ?>;">

    <div class="col-md-12" style="margin-top: 0px;">
        <h4 style="text-transform: uppercase;"><?php echo BookingTranslator::Booking_settings($lang) ?></h4>
    </div>
    <div class="col-md-12">
        <p id="separatorp"><?php echo BookingTranslator::Calendar_View($lang) ?></p>
        <div  class="btn-block" id="menu-button-div">
            <a href="bookingscheduling/<?php echo $id_space ?>"><?php echo BookingTranslator::Scheduling($lang) ?></a>      
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a href="bookingdisplay/<?php echo $id_space ?>"><?php echo BookingTranslator::Display($lang) ?></a><br/>
        </div>	
        <div  class="btn-block" id="menu-button-div">
            <a href="bookingrestrictions/<?php echo $id_space ?>"><?php echo BookingTranslator::Restrictions($lang) ?></a><br/>
        </div>	
        
        
        <div  class="btn-block" id="menu-button-div">
            <a href="bookingaccessibilities/<?php echo $id_space ?>"><?php echo BookingTranslator::Accessibilities($lang) ?></a><br/>
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a href="bookingnightwe/<?php echo $id_space ?>"><?php echo BookingTranslator::Nightwe($lang) ?></a><br/>
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a href="bookingcolorcodes/<?php echo $id_space ?>"><?php echo BookingTranslator::Color_codes($lang) ?></a>
            <a style="text-align: right;" href="bookingcolorcodeedit/<?php echo $id_space ?>/0">+</a><br/>
        </div>
    </div>

    <div class="col-md-12">
        <p id="separatorp"><?php echo BookingTranslator::Additional_info($lang) ?></p>
        <div  class="btn-block" id="menu-button-div">
            <a href="bookingsupsinfo/<?php echo $id_space ?>"><?php echo BookingTranslator::SupplementariesInfo($lang) ?></a>      
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a href="bookingpackages/<?php echo $id_space ?>"><?php echo BookingTranslator::Packages($lang) ?></a><br/>
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a href="bookingquantities/<?php echo $id_space ?>"><?php echo BookingTranslator::Quantities($lang) ?></a><br/>
        </div>
    </div>

    <div class="col-md-12">
        <p id="separatorp"><?php echo BookingTranslator::booking($lang) ?></p>
        <div  class="btn-block" id="menu-button-div">
            <a href="bookingblock/<?php echo $id_space ?>"><?php echo BookingTranslator::Block_Resouces($lang) ?></a>      
        </div>
    </div>

</div>

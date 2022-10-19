<?php
require_once 'Modules/core/Model/CoreConfig.php';
$modelCoreConfig = new CoreConfig();
$ecmenucolor = "";
$ecmenucolortxt = "";
if ($ecmenucolor == "") {
    $ecmenucolor = "#f1f1f1";
}
if ($ecmenucolortxt == "") {
    $ecmenucolortxt = "#000";
}
?>

    <style>
        #menu-button-div a{
            font: 12px Arial,sans-serif;
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
            font: 12px Arial,sans-serif;
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

<div  style="padding: 7px; background-color: <?php echo $ecmenucolor ?>; color:<?php echo $ecmenucolortxt ?>;">

    <div  style="margin-top: 0px;">
        <h4 style="text-transform: uppercase;"><?php echo ServicesTranslator::services($lang) ?></h4>
    </div>
    
    <?php
    $servicesuseproject = $modelCoreConfig->getParamSpace("servicesuseproject", $idSpace);
if ($servicesuseproject == 1) {
    ?>
    
    <div >
        <p id="separatorp"><?php echo ServicesTranslator::Projects($lang) ?></p>
        <div  class="btn-block" id="menu-button-div">
            <a href="servicesprojectsopened/<?php echo $idSpace ?>"><?php echo ServicesTranslator::Opened_projects($lang) ?></a>      
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a href="servicesprojectsclosed/<?php echo $idSpace ?>"><?php echo ServicesTranslator::Closed_projects($lang) ?></a><br/>
        </div>

        <div  class="btn-block" id="menu-button-div">
            <a href="servicesprojectedit/<?php echo $idSpace ?>/0"><?php echo ServicesTranslator::New_project($lang) ?></a><br/>
        </div>
    </div>
    
    <?php
}

$servicesusecommand = $modelCoreConfig->getParamSpace("servicesusecommand", $idSpace);
if ($servicesusecommand == 1) {
    ?>
    
    
    <div >
        <p id="separatorp"><?php echo ServicesTranslator::Opened_orders($lang) ?></p>
        <div  class="btn-block" id="menu-button-div">
            <a href="servicesordersopened/<?php echo $idSpace ?>"><?php echo ServicesTranslator::Opened_orders($lang) ?></a>      
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a href="servicesordersclosed/<?php echo $idSpace ?>"><?php echo ServicesTranslator::Closed_orders($lang) ?></a><br/>
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a href="servicesordersall/<?php echo $idSpace ?>"><?php echo ServicesTranslator::All_orders($lang) ?></a><br/>
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a href="servicesorderedit/<?php echo $idSpace ?>/0"><?php echo ServicesTranslator::New_orders($lang) ?></a><br/>
        </div>
    </div>
    
    <?php
}
?>
    
    <?php
$servicesusestock = $modelCoreConfig->getParamSpace("servicesusestock", $idSpace);
if ($servicesusestock == 1) {
    ?>
    <div >
        <p id="separatorp"><?php echo ServicesTranslator::Stock($lang) ?></p>
        <div  class="btn-block" id="menu-button-div">
            <a href="servicesstock/<?php echo $idSpace ?>"><?php echo ServicesTranslator::Stock($lang) ?></a>      
        </div>
        <br/>
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="servicespurchaseedit/<?php echo $idSpace ?>/0"><?php echo ServicesTranslator::New_Purchase($lang) ?></a><br/>
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a href="servicespurchase/<?php echo $idSpace ?>"><?php echo ServicesTranslator::Purchase($lang) ?></a>      
        </div>
    </div>
    
    <?php
}
?>
    
    <div >
        <p id="separatorp"><?php echo ServicesTranslator::Listing($lang) ?></p>

        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="serviceslisting/<?php echo $idSpace ?>"><?php echo ServicesTranslator::services($lang) ?></a>
            <a href="servicesedit/<?php echo $idSpace ?>/0">+</a>
        </div>	
    </div>
</div>

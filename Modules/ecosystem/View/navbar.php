<?php
require_once 'Modules/core/Model/CoreConfig.php';
$modelCoreConfig = new CoreConfig();
$ecmenucolor = "";//$modelCoreConfig->getParamSpace("ecosystemmenucolor", $id_space);
$ecmenucolortxt = "";//$modelCoreConfig->getParamSpace("ecosystemmenucolortxt", $id_space);
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
        <h4 style="text-transform: uppercase;"><?php echo EcosystemTranslator::Ecosystem($lang) ?></h4>
    </div>
    <div class="col-md-12">
        <p id="separatorp"><?php echo CoreTranslator::Belongings($lang) ?></p>
        <div  class="btn-block" id="menu-button-div">
            <a href="ecbelongingsedit/<?php echo $id_space ?>/0"><?php echo CoreTranslator::Neww($lang) ?></a>      
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="ecbelongings/<?php echo $id_space ?>"><?php echo CoreTranslator::Belongings($lang) ?></a><br/>
        </div>	
    </div>
    
    <div class="col-md-12">
        <p id="separatorp"><?php echo CoreTranslator::Units($lang) ?></p>
        <div  class="btn-block" id="menu-button-div">
            <a href="ecunitsedit/<?php echo $id_space ?>/0"><?php echo CoreTranslator::Neww($lang) ?></a>      
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="ecunits/<?php echo $id_space ?>"><?php echo CoreTranslator::Units($lang) ?></a><br/>
        </div>
    </div>
    
    <div class="col-md-12">
        <p id="separatorp"><?php echo CoreTranslator::Users($lang) ?></p>
        <div  class="btn-block" id="menu-button-div">
            <a href="ecusersedit/<?php echo $id_space ?>/0"><?php echo CoreTranslator::Neww($lang) ?></a>      
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="ecactiveusers/<?php echo $id_space ?>"><?php echo CoreTranslator::Active($lang) ?></a><br/>
        </div>
        <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="ecunactiveusers/<?php echo $id_space ?>"><?php echo CoreTranslator::Unactive($lang) ?></a><br/>
        </div>
    </div>
    
    <div class="col-md-12">
        <p id="separatorp"><?php echo CoreTranslator::Export($lang) ?></p>
        <div  class="btn-block" id="menu-button-div">
            <a href="ecexportresponsible/<?php echo $id_space ?>"><?php echo CoreTranslator::Responsible($lang) ?></a>      
        </div>
    </div>

</div>

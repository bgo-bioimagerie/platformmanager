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

<div class="col-md-2" style="padding: 7px; background-color: <?php echo $ecmenucolor ?>; color:<?php echo $ecmenucolortxt ?>;">

    <div class="col-md-12" style="margin-top: 0px;">

        <h4 style="text-transform: uppercase;"><?php echo BulletjournalTranslator::bulletjournal($lang) ?></h4>

    </div>
    
    <div class="col-md-3 col-md-offset-9">
        <a href="bulletjournalhelp/<?php echo $id_space ?>">
            <span class="bi-question-circle"></span>
        </a>
    </div>

    <div class="col-md-12">
    <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="bjnotes/<?php echo $id_space ?>"><?php echo BulletjournalTranslator::Notes($lang) ?></a><br/>
    </div>
    <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="bjcollections/<?php echo $id_space ?>"><?php echo BulletjournalTranslator::Collections($lang) ?></a>
            <a id="menu-button" href="bjcollectionsedit/<?php echo $id_space ?>/0">+</a><br/>
    
    </div>
    <div  class="btn-block" id="menu-button-div">
            <a id="menu-button" href="bjmigrations/<?php echo $id_space ?>"><?php echo BulletjournalTranslator::Migrations($lang) ?></a>
        </div>
    </div>
</div>

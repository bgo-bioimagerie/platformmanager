<?php
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/statistics/Model/StatisticsTranslator.php';
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
        <h4 style="text-transform: uppercase;"><?php echo InvoicesTranslator::invoices($lang) ?></h4>
    </div>
    <div class="col-md-12">
    <div  class="btn-block" id="menu-button-div">
                    <a href="<?php echo  "invoices/" . $id_space ?>"><?php echo InvoicesTranslator::All_invoices($lang) ?></a>      
                </div>
    </div>
 
<?php 
$modelSpace = new CoreSpace();
$menus = $modelSpace->getAllSpaceMenusModules($id_space);
$urls = array();
$urlss = array();
foreach($menus as $menu){
    $module = $menu["module"];
    $rootingFile = "Modules/" . $module . "/" . ucfirst($module) . "Invoices.php";
    //echo "rooting file = " . $rootingFile . "<br/>";
    if (file_exists($rootingFile)){
        //echo $rootingFile . " exists <br/>";
        require_once $rootingFile;
        $className = ucfirst($module)."Invoices";
        $classTranslator = ucfirst($module)."Translator";
        require_once 'Modules/' . $module . "/Model/" . $classTranslator . ".php"; 
        $translator = new $classTranslator();
        $model = new $className();
        $model->listRouts();
        if($model->count() > 0){
            ?>
            <div class="col-md-12">
                <p id="separatorp"><?php echo $translator->$module($lang) ?></p>
            <?php
        }
        for ($i = 0 ; $i < $model->count() ; $i++){
            $url = $model->getUrl($i);
            $txt = $translator->$url($lang);
            ?>
                <div  class="btn-block" id="menu-button-div">
                    <a href="<?php echo $url . "/" . $id_space ?>"><?php echo $txt ?></a>      
                </div>
            <?php  
        }  
        if($model->count() > 0){
            ?>
            </div>
            <?php    
        }
    }
}

?>

</div>
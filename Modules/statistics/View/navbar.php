<?php 
require_once 'Modules/core/Model/CoreConfig.php';
$modelCoreConfig = new CoreConfig();
$menucolor = $modelCoreConfig->getParam("statisticsmenucolor");
$menucolortxt = $modelCoreConfig->getParam("statisticsmenucolortxt");
if ($menucolor == ""){
	$menucolor = "#337ab7";
}
if($menucolortxt == ""){
	$menucolortxt = "#ffffff";
}
?>

<?php 
$modelSpace = new CoreSpace();
$menus = $modelSpace->getAllSpaceMenus($id_space);
$urls = array();
$urlss = array();
foreach($menus as $menu){
    $module = $menu["module"];
    $rootingFile = "Modules/" . $module . "/" . ucfirst($module) . "Statistics.php";
    //echo "rooting file = " . $rootingFile . "<br/>";
    if (file_exists($rootingFile)){
        //echo $rootingFile . " exists <br/>";
        require_once $rootingFile;
        $className = ucfirst($module)."Statistics";
        $classTranslator = ucfirst($module)."Translator";
        $translator = new $classTranslator();
        $model = new $className();
        $model->listRouts();
        for ($i = 0 ; $i < $model->count() ; $i++){
            $url = $model->getUrl($i);
            if(!in_array($url, $urlss)){
                $urlss[] = $url;
                $urls[] = array("url" => $url, "name" => $translator->$url($lang) );
            }
        }           
    }
}
$step = count($urls);
if (count($urls) > 3){
    $step = floor(count($urls)/3);
}

?>

<div class="col-md-12" style="padding: 7px; background-color: <?php echo $menucolor ?>; color:<?php echo $menucolortxt ?>;">
    
    <div class="col-md-2" style="margin-top: 0px;">
        <h2><?php echo StatisticsTranslator::Statistics($lang) ?></h2>
    </div>
    <div class="col-md-10">
        <div class="col-md-3">
            <?php for($i = 0 ; $i<$step ; $i++){
                //echo "i = " . $i . "<br/>";
                if($i < count($urls)){
                ?>
            
            <div class="btn-group" data-toggle="buttons">
            	<button onclick="location.href='<?php echo $urls[$i]["url"] ?>/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo $urls[$i]["name"] ?></button>
            </div>
                <?php
            }} ?>
        </div>
        <div class="col-md-3">
            <?php for($i = $step ; $i<2*$step ; $i++){
                //echo "i = " . $i . "<br/>";
                if($i < count($urls)){
                ?>
            
            <div class="btn-group" data-toggle="buttons">
            	<button onclick="location.href='<?php echo $urls[$i]["url"] ?>/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo $urls[$i]["name"] ?></button>
            </div>
                <?php
            }} ?>
        </div>
        <div class="col-md-3">
            <?php for($i = 2*$step ; $i<count($urls) ; $i++){
                //echo "i = " . $i . "<br/>";
                if($i < count($urls)){
                ?>
            
            <div class="btn-group" data-toggle="buttons">
            	<button onclick="location.href='<?php echo $urls[$i]["url"] ?>/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo $urls[$i]["name"] ?></button>
            </div>
                <?php
            }} ?>
        </div>
    </div>
    
</div>

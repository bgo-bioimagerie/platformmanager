<?php
require_once 'Modules/core/Model/CoreConfig.php';
$modelCoreConfig = new CoreConfig();
$menucolor = $modelCoreConfig->getParamSpace("databasemenucolor", $id_space);
$menucolortxt = $modelCoreConfig->getParamSpace("databasemenucolortxt", $id_space);
if ($menucolor == "") {
    $menucolor = "#337ab7";
}
if ($menucolortxt == "") {
    $menucolortxt = "#ffffff";
}
?>

<?php
$step = count($menu);
if (count($menu) > 3){
    $step = floor(count($menu)/3);
}
?>

<div class="col-md-12" style="padding: 7px; background-color: <?php echo $menucolor ?>; color:<?php echo $menucolortxt ?>;">
    
    <div class="col-md-2" style="margin-top: 0px;">
        <h2><?php echo $database["name"] ?></h2>
    </div>
    <div class="col-md-10">
        <div class="col-md-3">
            <?php for($i = 0 ; $i<$step ; $i++){
                //echo "i = " . $i . "<br/>";
                if($i < count($menu)){
                ?>
            
            <div class="btn-group" data-toggle="buttons">
            	<button onclick="location.href='<?php echo $menu[$i]["url"] ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo $menu[$i]["print_name"] ?></button>
            </div>
                <?php
            }} ?>
        </div>
        <div class="col-md-3">
            <?php for($i = $step ; $i<2*$step ; $i++){
                //echo "i = " . $i . "<br/>";
                if($i < count($menu)){
                ?>
            
            <div class="btn-group" data-toggle="buttons">
            	<button onclick="location.href='<?php echo $menu[$i]["url"] ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo $menu[$i]["print_name"] ?></button>
            </div>
                <?php
            }} ?>
        </div>
        <div class="col-md-3">
            <?php for($i = 2*$step ; $i<count($menu) ; $i++){
                //echo "i = " . $i . "<br/>";
                if($i < count($menu)){
                ?>
            
            <div class="btn-group" data-toggle="buttons">
            	<button onclick="location.href='<?php echo $menu[$i]["url"] ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo $menu[$i]["print_name"] ?></button>
            </div>
                <?php
            }} ?>
        </div>
    </div>
</div>
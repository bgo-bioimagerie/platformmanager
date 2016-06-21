<?php 
require_once 'Modules/core/Model/CoreConfig.php';
$modelCoreConfig = new CoreConfig();
$menucolor = $modelCoreConfig->getParam("resourcesmenucolor");
$menucolortxt = $modelCoreConfig->getParam("resourcesmenucolortxt");
if ($menucolor == ""){
	$menucolor = "#337ab7";
}
if($menucolortxt == ""){
	$menucolortxt = "#ffffff";
}
?>

<div class="col-md-12" style="padding: 7px; background-color: <?php echo $menucolor ?>; color:<?php echo $menucolortxt ?>;">
    
    <div class="col-md-2" style="margin-top: 0px;">
        <h2><?php echo ResourcesTranslator::resources($lang) ?></h2>
    </div>
    <div class="col-md-10">
        <div class="col-md-3">
            <!-- <legend><?php echo ResourcesTranslator::Area($lang) ?></legend> -->
            <div class="btn-group col-xs-12" data-toggle="buttons">
            	<button onclick="location.href='reareas/'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ResourcesTranslator::Areas($lang) ?></button>
		<button onclick="location.href='reareasedit/0'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;">+</button>
            </div>
            <br/>
            <div class="btn-group col-xs-12" data-toggle="buttons">
            	<button onclick="location.href='recategories/'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ResourcesTranslator::Categories($lang) ?></button>
		<button onclick="location.href='recategoriesedit/0'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;">+</button>
            </div>
        </div>
        <div class="col-md-3">
             <!--	<legend><?php echo ResourcesTranslator::Resources($lang) ?></legend> -->
            <div class="btn-group" data-toggle="buttons" style="width: 100%;">
		<button onclick="location.href='rerespsstatus'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ResourcesTranslator::Resps_Status($lang) ?> </button>
                <button onclick="location.href='rerespsstatusedit/0'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;">+</button>
            </div>
       </div>
        

        <div class="col-md-3">
         <div class="btn-group col-xs-12" data-toggle="buttons">
            	<button onclick="location.href='reeventtypes/'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ResourcesTranslator::Event_Types($lang) ?></button>
		<button onclick="location.href='reeventtypesedit/0'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;">+</button>
            </div>
            <br/>
            <div class="btn-group col-xs-12" data-toggle="buttons">
            	<button onclick="location.href='restates/'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ResourcesTranslator::States($lang) ?></button>
		<button onclick="location.href='restatesedit/0'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;">+</button>
            </div>		
        </div>
        
        <div class="col-md-3">
             <!--	<legend><?php echo ResourcesTranslator::Resources($lang) ?></legend> -->
            <div class="btn-group" data-toggle="buttons" style="width: 100%;">
		<button onclick="location.href='resources'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ResourcesTranslator::Resources($lang) ?> </button>
                <button onclick="location.href='resourcesedit/0'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;">+</button>
            </div>
       </div>
    </div>
    
</div>

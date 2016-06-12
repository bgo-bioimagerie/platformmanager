<?php 
require_once 'Modules/core/Model/CoreConfig.php';
$modelCoreConfig = new CoreConfig();
$ecmenucolor = $modelCoreConfig->getParam("ecosystemmenucolor");
$ecmenucolortxt = $modelCoreConfig->getParam("ecosystemmenucolortxt");
if ($ecmenucolor == ""){
	$ecmenucolor = "#337ab7";
}
if($ecmenucolortxt == ""){
	$ecmenucolortxt = "#ffffff";
}
?>

<div class="col-md-12" style="padding: 7px; background-color: <?php echo $ecmenucolor ?>; color:<?php echo $ecmenucolortxt ?>;">
    
    <div class="col-md-2" style="margin-top: 0px;">
        <h2><?php echo EcosystemTranslator::Ecosystem($lang) ?></h2>
    </div>
    <div class="col-md-10">
        <div class="col-md-4">
            <!-- <legend><?php echo  CoreTranslator::Belongings($lang) . " & " . CoreTranslator::Units($lang) ?></legend> -->
            <div class="btn-group" data-toggle="buttons">
                <button onclick="location.href='ecbelongings/'" class="btn btn-link" style="color: <?php echo  $ecmenucolortxt ?>;"><?php echo  CoreTranslator::Belongings($lang) ?></button>
                <button onclick="location.href='ecbelongingsedit/0'" class="btn btn-link" style="color: <?php echo  $ecmenucolortxt ?>;">+</button>
            </div>	
            <br/>
            <div class="btn-group" data-toggle="buttons">
                <button onclick="location.href='ecunits/'" class="btn btn-link" style="color: <?php echo  $ecmenucolortxt ?>;"><?php echo  CoreTranslator::Units($lang) ?></button>
                <button onclick="location.href='ecunitsedit/0'" class="btn btn-link" style="color: <?php echo  $ecmenucolortxt ?>;">+</button>
            </div>
        </div>
        <div class="col-md-4">
             <!--	<legend><?php echo  CoreTranslator::Users($lang) ?></legend> -->
            <div class="btn-group" data-toggle="buttons" style="width: 100%;">
					<button onclick="location.href='ecactiveusers'" class="btn btn-link" style="color: <?php echo  $ecmenucolortxt ?>;"><?php echo  CoreTranslator::Users($lang) ?> </button>
					<button onclick="location.href='ecusersedit/0'" class="btn btn-link" style="color: <?php echo  $ecmenucolortxt ?>;">+</button>
            </div>
                                        <br/>
                                <div class="btn-group" data-toggle="buttons" style="width: 100%;">
					<button onclick="location.href='ecunactiveusers'" class="btn btn-link" style="color: <?php echo  $ecmenucolortxt ?>;"><?php echo  CoreTranslator::Unactive_Users($lang) ?></button>
	</div>		
        </div>
        <div class="col-md-4">
           <!--  	<legend><?php echo  CoreTranslator::Export($lang) ?></legend> -->
					<button onclick="location.href='ecexportresponsible'" class="btn btn-link" style="color: <?php echo  $ecmenucolortxt ?>;"><?php echo  CoreTranslator::Responsible($lang) ?> </button>
			
        </div>
        
    </div>
    
</div>

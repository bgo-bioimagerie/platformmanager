<?php 
require_once 'Modules/core/Model/CoreConfig.php';
$modelCoreConfig = new CoreConfig();
$menucolor = $modelCoreConfig->getParamSpace("servicesmenucolor", $id_space);
$menucolortxt = $modelCoreConfig->getParamSpace("servicesmenucolortxt", $id_space);
if ($menucolor == ""){
	$menucolor = "#337ab7";
}
if($menucolortxt == ""){
	$menucolortxt = "#ffffff";
}
?>

<div class="col-md-12" style="padding: 7px; background-color: <?php echo $menucolor ?>; color:<?php echo $menucolortxt ?>;">
    
    <div class="col-md-2" style="margin-top: 0px;">
        <h2><?php echo ServicesTranslator::services($lang) ?></h2>
    </div>
    <div class="col-md-10">
        <div class="col-md-3">
            <div class="btn-group col-xs-12" data-toggle="buttons">
            	<button onclick="location.href='services/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ServicesTranslator::Services($lang) ?></button>
		<button onclick="location.href='servicesedit/<?php echo $id_space ?>/0'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;">+</button>
            </div>
            
        </div>
        <div class="col-md-3">
             <div class="btn-group col-xs-12" data-toggle="buttons">
            	<button onclick="location.href='servicesstock/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ServicesTranslator::Stock($lang) ?></button>
	 </div>
            <br/>
            <div class="btn-group col-xs-12" data-toggle="buttons">
            	<button onclick="location.href='servicespurchase/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ServicesTranslator::Purchase($lang) ?></button>
		<button onclick="location.href='servicespurchaseedit/<?php echo $id_space ?>/0'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;">+</button>
            </div>
       </div>
        

        <div class="col-md-3">
         <div class="btn-group col-xs-12" data-toggle="buttons">
            	<button onclick="location.href='servicesordersopened/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ServicesTranslator::Opened_orders($lang) ?></button>
		<br/>
                <button onclick="location.href='servicesordersclosed/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ServicesTranslator::Closed_orders($lang) ?></button>
                <br/>
            	<button onclick="location.href='servicesordersall/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ServicesTranslator::All_orders($lang) ?></button>
		<br/>
                <button onclick="location.href='servicesorderedit/<?php echo $id_space ?>/0'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ServicesTranslator::New_orders($lang) ?></button>
            </div>		
        </div>
        
        <div class="col-md-3">
         <div class="btn-group col-xs-12" data-toggle="buttons">
            	<button onclick="location.href='servicesprojectsopened/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ServicesTranslator::Opened_projects($lang) ?></button>
		<br/>
                <button onclick="location.href='servicesprojectsclosed/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ServicesTranslator::Closed_projects($lang) ?></button>
                <br/>
            	<button onclick="location.href='servicesprojectsall/<?php echo $id_space ?>'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ServicesTranslator::All_projects($lang) ?></button>
		<br/>
                <button onclick="location.href='servicesprojectedit/<?php echo $id_space ?>/0'" class="btn btn-link" style="color: <?php echo  $menucolortxt ?>;"><?php echo  ServicesTranslator::New_project($lang) ?></button>
            </div>		
        </div>
    </div>
    
</div>

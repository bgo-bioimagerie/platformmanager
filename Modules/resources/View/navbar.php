<?php 
require_once 'Modules/core/Model/CoreConfig.php';
$modelCoreConfig = new CoreConfig();
$ecmenucolor = $modelCoreConfig->getParam("resourcesmenucolor");
$ecmenucolortxt = $modelCoreConfig->getParam("resourcesmenucolortxt");
if ($ecmenucolor == ""){
	$ecmenucolor = "#337ab7";
}
if($ecmenucolortxt == ""){
	$ecmenucolortxt = "#ffffff";
}
?>

<head>

<style>
.bs-docs-header {
	position: relative;
	padding: 30px 15px;
	color: <?php echo  $ecmenucolortxt ?>;
	text-shadow: 0 1px 0 rgba(0, 0, 0, .1);
	background-color: <?php echo  $ecmenucolor ?>;
}

#navlink {
	color: <?php echo  $ecmenucolortxt ?>;
	text-shadow: 0 1px 0 rgba(0, 0, 0, .1);
}

.bs-docs-header {
	position: relative;
	color: <?php echo  $ecmenucolortxt ?>;
	text-shadow: 0 1px 0 rgba(0, 0, 0, .1);
	background-color: <?php echo  $ecmenucolor ?>;
}

#navlink {
	color: <?php echo  $ecmenucolortxt ?>;
	text-shadow: 0 1px 0 rgba(0, 0, 0, .1);
}

.well {
	color: <?php echo  $ecmenucolortxt ?>;
	background-color: <?php echo  $ecmenucolor ?>;
	border: none;
	-moz-box-shadow: 0px 0px 0px #000000;
        -webkit-box-shadow: 0px 0px 0px #000000;
        -o-box-shadow: 0px 0px 0px #000000;
        box-shadow: 0px 0px 0px #000000;
}

legend {
	color: <?php echo  $ecmenucolortxt ?>;
}
</style>

</head>

<?php 

$authorisations_location = $modelCoreConfig->getParam("sy_authorisations_location");

$classWell = 'col-md-4 well';
?>

<div class="bs-docs-header" id="content">
	<div class="container">
            <h2><?php echo  ResourcesTranslator::resources($lang) ?></h2>

		<div class=<?php echo  $classWell ?> >
			<fieldset>
				<legend><?php echo  ResourcesTranslator::Area($lang) ?></legend>
					<button onclick="location.href='reareas/'" class="btn btn-link" id="navlink"><?php echo  ResourcesTranslator::Areas($lang) ?></button>
					<button onclick="location.href='reareasedit/0'" class="btn btn-link" id="navlink">+</button>
                                <br/>
                                	<button onclick="location.href='recategories/'" class="btn btn-link" id="navlink"><?php echo  ResourcesTranslator::Categories($lang) ?></button>
					<button onclick="location.href='recategoriesedit/0'" class="btn btn-link" id="navlink">+</button>
                                
                        </fieldset>
		</div>
		
		<div class=<?php echo  $classWell ?>>
			<fieldset>
				<legend><?php echo  ResourcesTranslator::Resources($lang) ?></legend>
					<button onclick="location.href='resources'" class="btn btn-link" id="navlink"><?php echo  ResourcesTranslator::Resources($lang) ?> </button>
					<button onclick="location.href='resourcesedit/0'" class="btn btn-link" id="navlink">+</button>
			</fieldset>
		</div>
	</div>
</div>



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
		<h2><?php echo  CoreTranslator::Users($lang) ?></h2>

		<div class=<?php echo  $classWell ?> >
			<fieldset>
				<legend><?php echo  CoreTranslator::Belongings($lang) . " & " . CoreTranslator::Units($lang) ?></legend>
					<button onclick="location.href='ecbelongings/'" class="btn btn-link" id="navlink"><?php echo  CoreTranslator::Belongings($lang) ?></button>
					<button onclick="location.href='ecbelongingsedit/0'" class="btn btn-link" id="navlink">+</button>
				<br/>	
					<button onclick="location.href='ecunits/'" class="btn btn-link" id="navlink"><?php echo  CoreTranslator::Units($lang) ?></button>
					<button onclick="location.href='ecunitsedit/0'" class="btn btn-link" id="navlink">+</button>
			</fieldset>
		</div>
		
		<div class=<?php echo  $classWell ?>>
			<fieldset>
				<legend><?php echo  CoreTranslator::Users($lang) ?></legend>
					<button onclick="location.href='ecactiveusers'" class="btn btn-link" id="navlink"><?php echo  CoreTranslator::Users($lang) ?> </button>
					<button onclick="location.href='ecusersedit/0'" class="btn btn-link" id="navlink">+</button>
				<br/>
					<button onclick="location.href='ecunactiveusers'" class="btn btn-link" id="navlink"><?php echo  CoreTranslator::Unactive_Users($lang) ?></button>
			</fieldset>
		</div>
		
		<div class=<?php echo  $classWell ?>>
			<fieldset>
				<legend><?php echo  CoreTranslator::Export($lang) ?></legend>
					<button onclick="location.href='ecexportresponsible'" class="btn btn-link" id="navlink"><?php echo  CoreTranslator::Responsible($lang) ?> </button>
			</fieldset>
		</div>
		
	</div>
</div>




<head>

<style>
.bs-docs-header {
	position: relative;
	color: #cdbfe3;
	text-shadow: 0 1px 0 rgba(0, 0, 0, .1);
	background-color: #337ab7;
}

#navlink {
	color: #cdbfe3;
	text-shadow: 0 1px 0 rgba(0, 0, 0, .1);
}

.well {
	color: #cdbfe3;
	background-color: #337ab7;
	border: none;
        margin-bottom:0px;
}

legend {
	color: #ffffff;
}
</style>

</head>

<div style="background-color: #337ab7;">
	<div class="container">
		<h2 style="color: #fff;">Test API</h2>
		<div class="col-md-12">
		<div class='col-md-2 well'>
			<fieldset>
				<legend>Listing </legend>
					<button onclick="location.href='anticorps/<?php echo $id_space ?>/id'" class="btn btn-link" id="navlink">Anticorps</button>
					<button onclick="location.href='anticorpsedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>
				<br/>
					<button onclick="location.href='protocols/<?php echo $id_space ?>/id'" class="btn btn-link" id="navlink">Protocoles</button>
					<button onclick="location.href='protocolsedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>
			</fieldset>
		</div>
		
		<div class='col-md-2 well'>
			<fieldset>
			    <legend>Référence </legend>
				    <button onclick="location.href='sources/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Sources</button>
					<button onclick="location.href='sourcesedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>
				<br/>
				    <button onclick="location.href='isotypes/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Isotypes</button>
					<button onclick="location.href='isotypesedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>	
			</fieldset>
		</div>
		
		<div class='col-md-2 well'>
			<fieldset>
				<legend>Tissus </legend>	
				    <button onclick="location.href='especes/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Espèces</button>
					<button onclick="location.href='especesedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>
				<br/>		
				    <button onclick="location.href='organes/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Organes</button>
					<button onclick="location.href='organesedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>	
			</fieldset>
		</div>
		
		<div class='col-md-2 well'>
			<fieldset>
				<legend>Détails Proto </legend>	
				    <button onclick="location.href='kit/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">KIT</button>
					<button onclick="location.href='kitedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>
				<br/>		
			</fieldset>
		</div>
		
		<div class='col-md-2 well'>
			<fieldset>
				<legend> ... </legend>	
				    <button onclick="location.href='dem/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Dém</button>
					<button onclick="location.href='demedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>
				<br/>		
			</fieldset>
		</div>
		
                <div class='col-md-2 well'>
			<fieldset>
				<legend> ... </legend>	
				    <button onclick="location.href='application/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Application</button>
                                    <button onclick="location.href='applicationedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>
				
			</fieldset>
		</div>    
                    
		</div>
	</div>
</div>



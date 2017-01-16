
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

<?php //echo "id_space = " . $id_space . "<br/>" ?>

<div style="background-color: #337ab7;">
	<div class="container">
		<h2 style="color: #fff;">Anticorps</h2>
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
				<br/>		
				    <button onclick="location.href='prelevements/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Prélèvements</button>
					<button onclick="location.href='prelevementsedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>	
				<br/>		
				    <button onclick="location.href='status/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Status</button>
					<button onclick="location.href='statusedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>	
			</fieldset>
		</div>
		
		<div class='col-md-2 well'>
			<fieldset>
				<legend>Détails Proto </legend>	
				    <button onclick="location.href='kit/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">KIT</button>
					<button onclick="location.href='kitedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>
				<br/>		
				    <button onclick="location.href='proto/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Proto</button>
					<button onclick="location.href='protoedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>	
				<br/>		
				    <button onclick="location.href='fixative/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Fixative</button>
					<button onclick="location.href='fixativeedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>	
				<br/>		
				    <button onclick="location.href='option/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Option</button>
					<button onclick="location.href='optionedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>
				<br/>		
				    <button onclick="location.href='enzymes/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Enzyme</button>
					<button onclick="location.href='enzymesedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>	
			</fieldset>
		</div>
		
		<div class='col-md-2 well'>
			<fieldset>
				<legend> ... </legend>	
				    <button onclick="location.href='dem/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Dém</button>
					<button onclick="location.href='demedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>
				<br/>		
				    <button onclick="location.href='aciinc/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">AcI Inc</button>
					<button onclick="location.href='aciincedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>	
				<br/>		
				    <button onclick="location.href='linker/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Linker</button>
					<button onclick="location.href='linkeredit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>	
				<br/>		
				    <button onclick="location.href='inc/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Linker Inc</button>
					<button onclick="location.href='incedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>
				<br/>		
				    <button onclick="location.href='acii/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">acII</button>
					<button onclick="location.href='aciiedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>	
			</fieldset>
		</div>
		
                <div class='col-md-2 well'>
			<fieldset>
				<legend> ... </legend>	
				    <button onclick="location.href='application/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Application</button>
                                    <button onclick="location.href='applicationedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>
				<br/>		
				    <button onclick="location.href='staining/<?php echo $id_space ?>'" class="btn btn-link" id="navlink">Marquage</button>
                                    <button onclick="location.href='stainingedit/<?php echo $id_space ?>/0'" class="btn btn-link" id="navlink">+</button>	
				
			</fieldset>
		</div>    
                    
		</div>
	</div>
</div>



<?php include 'Modules/antibodies/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<head>

<script>
        function addRow(tableID) {

        	var idx = 1;
        	if(tableID == "dataTable"){
        		idx = 1;
            } 
            var table = document.getElementById(tableID);
 
            var rowCount = table.rows.length;
            //document.write(rowCount);
            var row = table.insertRow(rowCount);
            //document.write(row);
            var colCount = table.rows[idx].cells.length;
            //document.write(colCount);
 
            for(var i=0; i<colCount; i++) {
 
                var newcell = row.insertCell(i);
 
                newcell.innerHTML = table.rows[idx].cells[i].innerHTML;
                //alert(newcell.childNodes);
                switch(newcell.childNodes[0].type) {
                    case "text":
                            newcell.childNodes[0].value = "";
                            break;
                    case "checkbox":
                            newcell.childNodes[0].checked = false;
                            break;
                    case "select-one":
                            newcell.childNodes[0].selectedIndex = 0;
                            break;
                }
            }
        }
 
        function deleteRow(tableID) {
            try {

            var idx = 2;
            if(tableID == "dataTable"){
            	idx = 2;
            }     
            var table = document.getElementById(tableID);
            var rowCount = table.rows.length;
 
            for(var i=0; i<rowCount; i++) {
                var row = table.rows[i];
                var chkbox = row.cells[0].childNodes[0];
                if(null != chkbox && true == chkbox.checked) {
                    if(rowCount <= idx) {
                        alert("Cannot delete all the rows.");
                        break;
                    }
                    table.deleteRow(i);
                    rowCount--;
                    i--;
                }
 
 
            }
            }catch(e) {
                alert(e);
            }
        }
 
    </script>

</head>
									
<br>
<div class="col-lg-12">
	  <form role="form" class="form-horizontal" action="anticorpseditquery/<?php echo $id_space ?>/<?php echo $anticorps['id'] ?>" method="post" enctype="multipart/form-data">
		<div class="page-header">
			<h1>
				Edit Anticorps <br> <small></small>
			</h1>
		</div>
		
		<?php if($anticorps['id'] != ""){?>
		<div class="form-group">
			<label class="control-label col-xs-1">Id</label>
			<div class="col-xs-11">
			    <input class="form-control" id="id" type="text" name="id" value="<?php echo  $anticorps['id'] ?>" readonly
				/>
			</div>
		</div>
		<?php } ?>
		<br>
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-1">Nom</label>
			<div class="col-xs-11">
				<input class="form-control" id="nom" type="text" name="nom" value="<?php echo  $anticorps['nom'] ?>"
				/>
			</div>
		</div>
		<br>
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-1">No H2P2</label>
			<div class="col-xs-11">
				<input class="form-control" id="no_h2p2" type="text" name="no_h2p2" value="<?php echo  $anticorps['no_h2p2'] ?>"
				/>
			</div>
		</div>
		<br/>
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-1">Fournisseur</label>
			<div class="col-xs-11">
				<input class="form-control" id="fournisseur" type="text" name="fournisseur" value="<?php echo  $anticorps['fournisseur'] ?>"
				/>
			</div>
		</div>
		<br/>
		<!-- Source -->
		
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-1">Source</label>
			<div class="col-xs-11">
				<select class="form-control" name="id_source">
					<?php foreach($sourcesList as $source){
						$sourceID = $this->clean($source["id"]);
						$sourceName = $this->clean($source["nom"]);
						$selected = "";
						if ($anticorps["id_source"] == $sourceID){
							$selected = "selected=\"selected\"";
						}
						?>
						<OPTION value="<?php echo  $sourceID ?>" <?php echo  $selected ?>> <?php echo  $sourceName ?> </OPTION>
						<?php 
					}?>
  				</select>
			</div>
		</div>
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-1">Référence</label>
			<div class="col-xs-11">
				<input class="form-control" id="reference" type="text" name="reference" value="<?php echo  $anticorps['reference'] ?>"
				/>
			</div>
		</div>
				<br>
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-1">Clone</label>
			<div class="col-xs-11">
				<input class="form-control" id="clone" type="text" name="clone" value="<?php echo  $anticorps['clone'] ?>"
				/>
			</div>
		</div>
		<br>
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-1">Lot</label>
			<div class="col-xs-11">
				<input class="form-control" id="lot" type="text" name="lot" value="<?php echo  $anticorps['lot'] ?>"
				/>
			</div>
		</div>
		<br/>
		<!-- Isotype -->
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-1">Isotype</label>
			<div class="col-xs-11">
				<select class="form-control" name="id_isotype">
					<?php foreach($isotypesList as $isotype){
						$isotypeID = $this->clean($isotype["id"]);
						$isotypeName = $this->clean($isotype["nom"]);
						$selected = "";
						if ($anticorps["id_isotype"] == $isotypeID){
							$selected = "selected=\"selected\"";
						}
						?>
						<OPTION value="<?php echo  $isotypeID ?>" <?php echo  $selected ?>> <?php echo  $isotypeName ?> </OPTION>
						<?php 
					}?>
  				</select>
			</div>
		</div>
		<br>
		<!-- Stockage -->
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-1">Stockage</label>
			<div class="col-xs-11">
				<input class="form-control" id="stockage" type="text" name="stockage" value="<?php echo  $anticorps['stockage'] ?>"
				/>
			</div>
		</div>
		
		<!--   TISSUS    -->
		<div class="form-group">
			<label class="control-label col-xs-1">Tissus</label>
			<div class="col-xs-11">
				<table id="dataTable" class="table table-striped">
				<thead>
					<tr>
						<td></td>
						<td style="min-width:10em;">Référence protocole</td>
						<td style="min-width:10em;">Dilution</td>
						<td style="min-width:10em;">Commentaire</td>
						<td style="min-width:10em;">Espece</td>
						<td style="min-width:10em;">Organe</td>
						<td style="min-width:10em;">Validé</td>
						<td style="min-width:10em;">Référence bloc</td>
						<td style="min-width:10em;">Prélèvement</td>
                                                <td style="min-width:10em;">image</td>
					</tr>
				</thead>
					<tbody>
						<?php 
						foreach ($anticorps['tissus'] as $tissus){
													
							?>
							<tr>
								<td><input type="checkbox" name="chk" /></td>
								
								<td>
									<select class="form-control" name="ref_protocol[]">
									<?php 
									$ref_proto = $this->clean($tissus["ref_protocol"]);
									foreach ($protocols as $protocol){
										$id_proto = $this->clean($protocol["id"]);
										$no_proto = $this->clean($protocol["no_proto"]);
										$selected = "";
										if ($ref_proto == $no_proto){
											$selected = "selected=\"selected\"";
										}
										?>
										<OPTION value="<?php echo $no_proto?>" <?php echo $selected?>> <?php echo  $no_proto ?> </OPTION>
										<?php 
									}	
									?>
									</select>
								</td>
								<td><input class="form-control" type="text" name="dilution[]" value="<?php echo  $tissus["dilution"] ?>"/></td>
								<td>
									<textarea name="comment[]"><?php echo  $this->clean($tissus["comment"])  ?></textarea>
								</td>
								<td>
									<select class="form-control" name="espece[]">
									<?php 
									$espaceid = $this->clean($tissus["espece_id"]);
									foreach ($especes as $espece){
										$ide = $this->clean($espece["id"]);
										$namee = $this->clean($espece["nom"]);
										$selected = "";
										if ($espaceid == $ide){
											$selected = "selected=\"selected\"";
										}
										?>
										<OPTION value="<?php echo $ide?>" <?php echo $selected?>> <?php echo  $namee ?> </OPTION>
										<?php 
									}	
									?>
									</select>
								</td>
								<td>
									<select class="form-control" name="organe[]">
									<?php 
									$organe_id = $this->clean($tissus["organe_id"]);
									
									foreach ($organes as $organe){
										$ide = $this->clean($organe["id"]);
										$namee = $this->clean($organe["nom"]);
										$selected = "";
										if ($organe_id == $ide){
											$selected = "selected=\"selected\"";
										}
										?>
										<OPTION value="<?php echo $ide?>" <?php echo $selected?>> <?php echo  $namee ?> </OPTION>
										<?php 
									}	
									?>
									</select>
								</td>
								<td><select class="form-control" name="status[]" >
								<?php 
								foreach ($status as $statu){
								?>
									<option value="<?php echo  $statu["id"] ?>" <?php if ($tissus["status"] == $statu["id"]){echo "selected=\"selected\"";}?>><?php echo $statu["nom"]?></option>
								<?php	
								}
								?>
								<!--
								<option value="1" <?php if ($tissus["status"] == "1"){echo "selected=\"selected\"";}?>>Validé</option>
								<option value="2" <?php if ($tissus["status"] == "2"){echo "selected=\"selected\"";}?>>Non validé</option>
								<option value="3" <?php if ($tissus["status"] == "3"){echo "selected=\"selected\"";}?>>Non testé</option>
								 -->
								</select></td>
								<td><input class="form-control" type="text" name="ref_bloc[]" value="<?php echo  $tissus["ref_bloc"] ?>"/></td>
								<td>
									<select class="form-control" name="prelevement[]">
									<?php 
									$prelev = $this->clean($tissus["prelevement_id"]);
									foreach ($prelevements as $prelevement){
										$id_prelevement = $this->clean($prelevement["id"]);
										$nom_prelevement = $this->clean($prelevement["nom"]);
										$selected = "";
										if ($prelev == $id_prelevement){
											$selected = "selected=\"selected\"";
										}
										?>
										<OPTION value="<?php echo $id_prelevement?>" <?php echo $selected?>> <?php echo  $nom_prelevement ?> </OPTION>
										<?php 
									}	
									?>
									</select>
								</td>
                                                                <td>
                                                                   <input type="file" name="tissusfiles[]">
                                                                   <a href="data/antibodies/<?php echo $tissus["image_url"] ?>"><?php echo $tissus["image_url"] ?></a>
                                                                </td>
								
								<!-- 
								<td><input class="form-control" type="text" name="temps_incubation[]" value="<?php echo  $tissus["temps_incubation"] ?>"/></td>
								 -->
								
								
								
							</tr>
							<?php
						}
						?>
						<?php 
						if (count($anticorps['tissus']) < 1){
						?>
						<tr>
							<td><input type="checkbox" name="chk" /></td>
							<td>
								<select class="form-control" name="ref_protocol[]">
									<?php 
									foreach ($protocols as $protocol){
										$no_proto = $this->clean($protocol["no_proto"]);
										$idproto = $this->clean($protocol["id"]);
										?>
										<OPTION value="<?php echo $no_proto?>"> <?php echo  $no_proto ?> </OPTION>
										<?php 
									}	
									?>
								</select>
							</td>
							<td><input class="form-control" type="text" name="dilution[]" /></td>
							<td>
							<textarea name="comment[]">
								
							</textarea>
							</td>
							<td>
								<select class="form-control" name="espece[]">
									<?php
									foreach ($especes as $espece){
										$ide = $this->clean($espece["id"]);
										$namee = $this->clean($espece["nom"]);
										?>
										<OPTION value="<?php echo $ide?>"> <?php echo  $namee ?> </OPTION>
										<?php 
									}	
									?>
									</select>	
							</td>
							<td>
								<select class="form-control" name="organe[]">
									<?php 
									foreach ($organes as $organe){
										$ide = $this->clean($organe["id"]);
										$namee = $this->clean($organe["nom"]);
										?>
										<OPTION value="<?php echo $ide?>" > <?php echo  $namee ?> </OPTION>
										<?php 
									}	
									?>
									</select>
							</td>
							<td><select class="form-control" name="status">
								<?php 
								foreach ($status as $statu){
								?>
									<option value="<?php echo  $statu["id"] ?>"><?php echo $statu["nom"]?></option>
								<?php	
								}
								?>
							<!-- 
							<option value="1" >Validé</option>
							<option value="2" >Non validé</option>
							<option value="3" selected="selected">Non testé</option>
							-->
							
							</select></td>
							<td><input class="form-control" type="text" name="ref_bloc[]" /></td>
							<td>
								<select class="form-control" name="prelevement[]">
								<?php 
								foreach ($prelevements as $prelevement){
									$id_prelevement = $this->clean($prelevement["id"]);
									$nom_prelevement = $this->clean($prelevement["nom"]);
									?>
									<OPTION value="<?php echo $id_prelevement?>"> <?php echo  $nom_prelevement ?> </OPTION>
									<?php 
								}	
								?>
								</select>
							</td>
							<td>
                                                            <input type="file" name="tissusfiles[]">
                                                        </td>
							<!-- 
							<td><input class="form-control" type="text" name="temps_incubation[]" /></td>
							 -->

						</tr>
						<?php 
						}
						?>
					</tbody>
				</table>
				
				<div class="col-md-6">
					<input type="button" class="btn btn-default" value="Ajouter Tissus"
						onclick="addRow('dataTable')" /> 
					<input type="button" class="btn btn-default" value="Enlever Tissus"
						onclick="deleteRow('dataTable')" /> <br>
				</div>
			</div>
		</div>
			
		<!-- ADD HERE PROPRIO ADD  -->
		<div class="form-group">
			<label class="control-label col-xs-1">Propriétaire</label>
			<div class="col-xs-11">
				<table id="proprioTable" class="table table-striped">
					<thead>
						<tr>
							<td></td>
							<td>Propriétaire</td>
							<td>Disponibilité</td>
							<td>Date réception</td>
							<td>No Dossier</td>
						</tr>
					</thead>
					<tbody>
						
							<?php 
							foreach ($anticorps['proprietaire'] as $proprio){
								
								//print_r($proprio);
								?>
								<tr>
								<td><input type="checkbox" name="chk" /></td>
								<td>
									<select class="form-control" name="id_proprietaire[]">
									<?php 
									
									$pid = $this->clean($proprio["id_user"]);
									foreach ($users as $user){
										$uid = $this->clean($user["id"]);	
										$uname = $this->clean($user["name"]) . " " . $this->clean($user["firstname"]) ;
										$selected = "";
										if ($pid == $uid){
											$selected = "selected=\"selected\"";
										}
										?>
										<OPTION value="<?php echo $uid?>" <?php echo $selected?>> <?php echo  $uname ?> </OPTION>
										<?php 
									}	
									?>
									</select>	
								</td>
								<td>
									<select class="form-control" name="disponible[]">
										<OPTION value="1" <?php if ($proprio["disponible"] == 1){echo "selected=\"selected\"";}?>> disponible </OPTION>
										<OPTION value="2" <?php if ($proprio["disponible"] == 2){echo "selected=\"selected\"";}?>> épuisé </OPTION>
										<OPTION value="3" <?php if ($proprio["disponible"] == 3){echo "selected=\"selected\"";}?>> récupéré par équipe </OPTION>
									</select>	
								</td>
								<td>
									<input class="form-control" type="text" name="date_recept[]" value="<?php echo  CoreTranslator::dateFromEn($proprio["date_recept"], $lang) ?>"/>	
								</td>
								<td>
									<input class="form-control" type="text" name="no_dossier[]" value="<?php echo  CoreTranslator::dateFromEn($proprio["no_dossier"], $lang) ?>"/>	
								</td>
								<tr />
							<?php
							} 
							?>	
							<?php 
							if(count($anticorps['proprietaire']) < 1){
							?>
								<tr>
							    <td><input type="checkbox" name="chk" /></td>
								<td>
									<select class="form-control" name="id_proprietaire[]">
									<?php
									foreach ($users as $user){
										$uid = $this->clean($user["id"]);
										$uname = $this->clean($user["name"]) . " " . $this->clean($user["firstname"]) ;
										?>
										<OPTION value="<?php echo $uid?>"> <?php echo  $uname ?> </OPTION>
									<?php 
									}	
									?>
									</select>	
								</td>
								<td>
									<select class="form-control" name="disponible[]">
										<OPTION value="1"> disponible </OPTION>
										<OPTION value="2"> épuisé </OPTION>
										<OPTION value="3"> récupéré par équipe </OPTION>
									</select>	
								</td>
								<td>
									<input class="form-control" type="text" name="date_recept[]" />
								</td>
								<td>
									<input class="form-control" type="text" name="no_dossier[]"/>	
								</td>
								<tr />
							<?php	
							}
							?>
				
					</tbody>
				</table>
				
				<div class="col-md-6">
					<input type="button" class="btn btn-default" value="Ajouter Propriétaire"
						onclick="addRow('proprioTable')" /> 
					<input type="button" class="btn btn-default" value="Enlever Propriétaire"
						onclick="deleteRow('proprioTable')" /> <br> 
				</div>
			</div>
		</div>
		
		<br>
		<!-- Export catalog -->
		<div class="form-group">
                    <label class="control-label col-xs-1">Catalogue</label>
                    <div class="col-xs-11">
                        <div class="checkbox">
			<label>
                            <?php if ( $anticorps['export_catalog'] ){  
                                $checked = "checked"; 
                            } 
                            else {
                                $checked = "";
                            } 
                            ?>
                            <input type="checkbox" name="export_catalog" <?php echo $checked ?>> Apparait dans le catalogue
			</label>
                        </div>
                    </div>
		</div>
		<!-- Application -->
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-1">Application</label>
			<div class="col-xs-11">
				<select class="form-control" name="id_application">
					<?php foreach($applicationsList as $application){
						$appID = $this->clean($application["id"]);
						$appName = $this->clean($application["name"]);
						$selected = "";
						if ($anticorps["id_application"] == $appID){
							$selected = "selected=\"selected\"";
						}
						?>
						<OPTION value="<?php echo  $appID ?>" <?php echo  $selected ?>> <?php echo  $appName ?> </OPTION>
						<?php 
					}?>
  				</select>
			</div>
		</div>
                <!-- Staining -->
		<div class="form-group">
			<label class="control-label col-xs-1">Marquage</label>
			<div class="col-xs-11">
				<select class="form-control" name="id_staining">
					<?php foreach($stainingsList as $staining){
						$stainingID = $this->clean($staining["id"]);
						$stainingName = $this->clean($staining["name"]);
						$selected = "";
						if ($anticorps["id_staining"] == $stainingID){
							$selected = "selected=\"selected\"";
						}
						?>
						<OPTION value="<?php echo  $stainingID ?>" <?php echo  $selected ?>> <?php echo  $stainingName ?> </OPTION>
						<?php 
					}?>
  				</select>
			</div>
		</div>
	
		<!-- Buttons -->
		<div class="col-xs-2 col-xs-offset-10" id="button-div">
		        <input type="submit" class="btn btn-primary" value="Edit" />
				<button type="button" onclick="location.href='anticorps/<?php echo $id_space ?>'" class="btn btn-default">Annuler</button>
		</div>
		<div class="col-xs-11 col-xs-offset-1" id="button-div">
		        <?php if($anticorps['id'] != ""){ ?>
		        	<button type="button" onclick="location.href='<?php echo "anticorpsdelete/".$id_space."/".$anticorps['id'] ?>'" class="btn btn-danger">Supprimer</button>
				<?php }?>
		</div>
      </form>
</div>

<?php endblock();

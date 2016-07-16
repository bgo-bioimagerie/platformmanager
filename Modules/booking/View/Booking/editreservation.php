<?php include 'Modules/booking/View/layout.php' ?>

<!-- body -->     
<?php startblock('content') ?>

<?php
$readOnlyGlobal = ""; 
if (!$canEditReservation){
	$readOnlyGlobal = "readonly";
}
?>

<header>  
    
<script type="text/javascript">
    	function ConfirmDelete()
    	{
            if (confirm("Are you sure you want to Delete this reseration ?")){
                location.href='bookingremoveentry/<?php echo $this->clean($reservationInfo['id']) ?>';
            }
    	}
</script>  

<style>

<?php 
if (!isset($reservationInfo) ){
?>
	#package_div {
	    display: none;
	} 
<?php 
}
else{
	if ( $reservationInfo["package_id"] == 0 && $resourceInfo["use_package"] < 2){
	?>
	#package_div {
	    display: none;
	} 
<?php 
}
else{
?>
	#resa_time_div {
	    display: none;
	}
<?php
}
}
?>
	
</style>
</header>

<br>
<div class="container">
	<div class="col-md-8 col-md-offset-2">
	<form role="form" class="form-horizontal" action="bookingeditreservationquery"
		method="post">
	
		<div class="page-header">
			<h1>
				<?php echo BookingTranslator::Edit_Reservation($lang); ?>
				<br> <small></small>
			</h1>
		</div>

	    <input class="form-control" id="id" type="hidden"  name="resource_id" value="<?php echo $this->clean($resourceBase['id']) ?>" <?php echo $readOnlyGlobal?>/>
		
		<div class="form-group">
			<label class="control-label col-xs-4"><?php echo BookingTranslator::Resource($lang)?></label>
			<div class="col-xs-8">
				<input class="form-control" id="id" type="text"  name="resource_name" value="<?php echo $this->clean($resourceBase['name']) ?>" readonly/>
			</div>
		</div>
	
		<?php if (isset($reservationInfo)){
			?>
			<div class="form-group">
				<label class="control-label col-xs-4"><?php echo BookingTranslator::Reservation_number($lang)?></label>
				<div class="col-xs-8">
				<input class="form-control" id="id" type="text"  name="reservation_id" value="<?php echo $this->clean($reservationInfo['id']) ?>" readonly/>
				</div>
			</div>
			<?php 		
		}
		?>
	
	    <div class="form-group">
			<label class="control-label col-xs-4"><?php echo BookingTranslator::booking_on_behalf_of($lang)?></label>
			<div class="col-xs-8">
					<?php 
					$allowedBookForOther = true;
					if ( $this->clean($curentuser['status_id']) < 3){
						$allowedBookForOther = false;
					}
					
					$recipientID = 0;
					if(isset($reservationInfo)){
						$recipientID = $this->clean($reservationInfo["recipient_id"]);
					}
					if ($allowedBookForOther==false && isset($reservationInfo) && $recipientID != $this->clean($curentuser['id'])){
						?>
						<select class="form-control" name="recipient_id" disabled="disabled">
							<?php
							foreach ($users as $user){
								$userId = $this->clean($user['id']);
								$userName = $this->clean($user['name']) . " " . $this->clean($user['firstname']);
								$selected = "";
								if ($userId == $recipientID){
									?>
									<OPTION value="<?php echo  $userId ?>"> <?php echo  $userName?> </OPTION>
									<?php
								} 
							}
							?>
						</select>
						
						<?php
					}
					else{
					?>
					
					<select class="form-control" name="recipient_id">
						<?php
						if ($allowedBookForOther){
							$recipientID = $this->clean($reservationInfo["recipient_id"]);
							if ($recipientID == "" && $recipientID == 0){
								$recipientID = $this->clean($curentuser['id']); 
							} 
							foreach ($users as $user){
								$userId = $this->clean($user['id']);
								$userName = $this->clean($user['name']) . " " . $this->clean($user['firstname']);
								$selected = "";
								if ($userId == $recipientID){
									$selected = "selected=\"selected\"";
								}
								?>
								<OPTION value="<?php echo  $userId ?>" <?php echo  $selected ?>> <?php echo  $userName?> </OPTION>
								<?php 
							}
						}
						else{
							?>
							<OPTION value="<?php echo  $this->clean($curentuser['id']) ?>"> <?php echo $this->clean($curentuser['name']) . " " . $this->clean($curentuser['firstname'])?> </OPTION>
							<?php
						}
					}
						?>
				</select>
			</div>
		</div>
		
		<?php if( count($responsiblesList) > 1 ){ ?>
		<div class="form-group">
			<label class="control-label col-xs-4"><?php echo BookingTranslator::Responsible($lang)?></label>
			<div class="col-xs-8">
				<select class="form-control" name="responsible_id">
				<?php   
				foreach($responsiblesList as $resp){
                                   
					$selected = "";
					if ($resp['id'] == $reservationInfo["responsible_id"]){
                                            $selected = "selected=\"selected\"";
					}
					?>
					<OPTION value="<?php echo $this->clean($resp['id']) ?>" <?php echo  $selected ?> > <?php echo $this->clean($resp['fullname'])?> </OPTION>
					<?php
				}
				?>
				</select>
			</div>
		</div>
		<?php } ?>
		
		<?php 
		$modelCoreConfig = new CoreConfig();
		$editBookingDescriptionSettings = $modelCoreConfig->getParam("SyDescriptionFields");
	
		$shortDescName = BookingTranslator::Short_description($lang);
		$fullDescName = BookingTranslator::Full_description($lang);
		if ($editBookingDescriptionSettings > 1){
			$shortDescName = BookingTranslator::Description($lang);
			$fullDescName = BookingTranslator::Description($lang);
		}
		
		?>
		
		<?php if ($projectsList == ""){?>
		
			<?php if ($editBookingDescriptionSettings == 1 || $editBookingDescriptionSettings == 2){?>
			<div class="form-group">
				<label for="inputEmail" class="control-label col-xs-4"><?php echo  $shortDescName ?></label>
				<div class="col-xs-8">
					<input class="form-control" id="name" type="text" name="short_description"
					       value="<?php if (isset($reservationInfo)){ echo $this->clean($reservationInfo['short_description']);} ?>" 
					       <?php echo $readOnlyGlobal?> 
					/>
				</div>
			</div>
			<?php 
			}
		}
		else{
			?>
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-4">Project</label>
			<div class="col-xs-8">
				<select class="form-control" name="short_description">
				<?php 
				$curentProject = $this->clean($reservationInfo['short_description']);
				foreach ($projectsList as $project){
					$projectID = $this->clean($project["id"]);
					$projectName = $this->clean($project["name"]);
					$selected = "";
					if ($curentProject == $projectID){
						$selected = "selected=\"selected\"";
					}
					?>
					<OPTION value="<?php echo  $projectID ?>" <?php echo  $selected ?>> <?php echo  $projectName?> </OPTION>
					<?php
				}
				?>
				</select>
			</div>
		</div>
		<?php
		}
		?>
		
		<?php if ($editBookingDescriptionSettings == 1 || $editBookingDescriptionSettings == 3){?>
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-4"><?php echo  $fullDescName ?></label>
			<div class="col-xs-8">
				<textarea class="form-control" id="name" name="full_description" <?php echo $readOnlyGlobal?>
				><?php if (isset($reservationInfo)){ echo $this->clean($reservationInfo['full_description']);} ?></textarea>
			</div>
		</div>
		<?php }?>
		
		<!-- Supplementary cal info -->
		<?php 
		foreach ($calSups as $calSup){
			$star = "";
			$required = "";
			if ($calSup["mandatory"] == 1){
				$star = "*";
				$required = "required"; 
			}
		?>
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-4"><?php echo $calSup["name"] .$star ?></label>
			<div class="col-xs-8">
			        <input type='hidden' name="calsupName[]" value="<?php echo  $calSup["name"] ?>"/>
			        <?php 
			        $calSupDataHere = "";
				    if (isset($calSupsData[$calSup["name"]])){
				    	$calSupDataHere = $this->clean($calSupsData[$calSup["name"]]);
				    }
					if ($calSupDataHere == "" && $calSup["name"] == "tel"){
						$calSupDataHere = $curentuser["tel"];
					}
			        ?>
					<input type='text' class="form-control" name="calsupValue[]"
					       value="<?php  ?>" <?php echo $readOnlyGlobal?> <?php echo $required?>/>
		    </div>	
		</div>
		<?php	
		}
		?>
		
		
		
		
		<!-- RESERVATION TIME -->
		<div class="form-group">
				<?php 
				if (isset($reservationInfo)){
					$stime = $this->clean($reservationInfo['start_time']);
					$sdate = date("Y-m-d", $stime);
					$sh = date("H", $stime);
					$sm = date("i", $stime);
				}
				else{
					$sdate = $date;
					$sh = $timeBegin['h'];
					$sm = $timeBegin['m'];
				}
				?>
			<label for="inputEmail" class="control-label col-xs-4"><?php echo BookingTranslator::Beginning_of_the_reservation($lang)?>:</label>
			<div class="col-xs-8">
				<div class='input-group date form_date_<?php echo  $lang ?>'>
					<input type='text' class="form-control" name="begin_date"
					       value="<?php echo  CoreTranslator::dateFromEn($sdate, $lang) ?>" <?php echo $readOnlyGlobal?>/>
					<span class="input-group-addon">
						<span class="glyphicon glyphicon-calendar"></span>
					</span>
				</div>
		    </div>
		</div>
		<div class="form-group">    
			<div class="col-xs-8 col-xs-offset-4">
				<!-- time -->
				
				<label for="inputEmail" class="control-label col-xs-4"><?php echo BookingTranslator::time($lang)?>:</label>
				
				<div class="col-xs-3">
				<input class="form-control" id="name" type="text" name="begin_hour"
				       value="<?php echo  $sh ?>" <?php echo $readOnlyGlobal?> 
				/>
				</div>
				<div class="col-xs-1">
				<b>:</b>
				</div>
				<div class="col-xs-3">
				<input class="form-control" id="name" type="text" name="begin_min"
				       value="<?php echo  $sm ?>"  <?php echo $readOnlyGlobal?>
				/>
				</div>
			</div>
		</div>
		
		
		<?php 
		if (count($packages) > 0){
                    if ($resourceBase["use_package"] < 2){
		?>	
		<div class="checkbox col-xs-8 col-xs-offset-4">
    		<label>
    		<?php 
    		$checked = "";
    		if (isset($reservationInfo)){
	    		if ($reservationInfo["package_id"] > 0){
	    			$checked = "checked";	
	    		}
    		}
    		?>
                    <input id="use_package" type="checkbox" name="use_package" value="yes" <?php echo $checked?>> <?php echo  BookingTranslator::Use_Package($lang) ?>
			</label>
  		</div>
                
                    <?php } ?>
  		
  		<?php 
  		if ($resourceBase["use_package"] < 2){
  			?>
  			<div id="package_div">
  			<?php 
  		}
  		else{
  			?>
  			<div>
  			<input type="hidden" name="use_package" value="yes" >
  			<?php 
  		}
  		?>
  			<div class="form-group">
				<label class="control-label col-xs-4"><?php echo BookingTranslator::Select_Package($lang)?></label>
				<div class="col-xs-8">
					<select class="form-control" name="package_choice">
						<?php 
						foreach($packages as $package){
							$selected = "";
							if (isset($reservationInfo)){
								if($reservationInfo["package_id"]==$package["id"]){
									$selected = "selected=\"selected\"";
								}
							}
							?>
							<OPTION value="<?php echo $package["id"]?>" <?php echo  $selected ?> > <?php echo  $package["name"] ?> </OPTION>
							<?php
						}
						?>
					</select>
				</div>
			</div>
  		
  		</div>
		<?php
                    }
	
		?>
		
                <?php 
                   if ($resourceBase["use_package"] < 2){ // if not "use only package"
		?>
                            
		<div id="resa_time_div">
		<?php 
		
		if( $this->clean($scheduling["resa_time_setting"]) == 1){
			?>
			<div class="form-group">
					<?php 
					if (isset($reservationInfo)){
						$etime = $this->clean($reservationInfo['end_time']);
						$edate = date("Y-m-d", $etime);
						$eh = date("H", $etime);
						$em = date("i", $etime);
					}
					else{
						$edate = $date;
						$eh = $timeEnd['h'];
						$em = $timeEnd['m'];
					}
					?>
				<label for="inputEmail" class="control-label col-xs-4"><?php echo BookingTranslator::End_of_the_reservation($lang)?>:</label>
				<div class="col-xs-8">
					<div class='input-group date form_date_<?php echo  $lang ?>'>
						<input type='text' class="form-control" data-date-format="YYYY-MM-DD" name="end_date"
						       value="<?php echo  CoreTranslator::dateFromEn($edate, $lang) ?>" <?php echo $readOnlyGlobal?>/>
						<span class="input-group-addon">
							<span class="glyphicon glyphicon-calendar"></span>
						</span>
					</div>
			    </div>
			</div>
			<div class="form-group">
				<div class="col-xs-8 col-xs-offset-4">
					<!-- time -->
					<label for="end_hour" class="control-label col-xs-4"><?php echo BookingTranslator::time($lang)?>:</label>
					
					<div class="col-xs-3">
					<input class="form-control" id="name" type="text" name="end_hour"
					       value="<?php echo  $eh ?>"  <?php echo $readOnlyGlobal?>
					/>
					</div>
					<div class="col-xs-1">
					<b>:</b>
					</div>
					<div class="col-xs-3">
					<input class="form-control" id="name" type="text" name="end_min"
					       value="<?php echo  $em ?>"  <?php echo $readOnlyGlobal?>
					/>
					</div>
				</div>
			</div>
		<?php 
		}
		else{
                        if ($id_new_resa){
                            if ($scheduling["booking_time_scale"] == 1){
                                $durationPrint = 30;
                                $viewDuration = 1;
                            }
                            else if ($scheduling["booking_time_scale"] == 2){
                                $durationPrint = 1;
                                $viewDuration = 2;
                            }
                            else if ($scheduling["booking_time_scale"] == 3){
                                $durationPrint = 1;
                                $viewDuration = 3;
                            }
                        }
                        else{
                            $duration = 30*60;
                            if (isset($reservationInfo)){
                                    $duration = $this->clean($reservationInfo['end_time']) - $this->clean($reservationInfo['start_time']);
                            }

                            $viewDuration = 1;
                            $durationPrint = $duration/60;
                            if ( $duration/60 >= 120 ){
                                    $viewDuration = 2;
                                    $durationPrint = $duration/3600;
                            }
                            if ( $duration/(3600) >= 48 ){
                                    $viewDuration = 3;
                                    $durationPrint = $duration/(3600*24);
                            }
                        }
			?>
			<div class="form-group">
				<label class="control-label col-xs-4"><?php echo BookingTranslator::Duration($lang)?></label>
				<div class="col-xs-4">
					<input class="form-control" id="name" type="text" name="duration"
					       value="<?php echo  $durationPrint ?>" 
					/>
				</div>
				<div class="col-xs-4">
					<select class="form-control" name="duration_step">
						<OPTION value="1" <?php if($viewDuration==1){echo "selected=\"selected\"";} ?>> <?php echo  BookingTranslator::Minutes($lang) ?> </OPTION>
						<OPTION value="2" <?php if($viewDuration==2){echo "selected=\"selected\"";} ?>> <?php echo  BookingTranslator::Hours($lang) ?> </OPTION>
						<OPTION value="3" <?php if($viewDuration==3){echo "selected=\"selected\"";} ?>> <?php echo  BookingTranslator::Days($lang) ?> </OPTION>
					</select>
				</div>
			</div>
		<?php
		}
		?>
                    </div>
                    <?php 
		} // end if use only package
		?>
		
		<!-- color code -->
		
		<div class="form-group">
			<label for="inputEmail" class="control-label col-xs-4"><?php echo BookingTranslator::Color_code($lang)?></label>
			<div class="col-xs-8">
			<select class="form-control" name="color_code_id" <?php echo $readOnlyGlobal?>>
			<?php 
			$colorID = $resourceInfo["default_color_id"];
			
			
			if (isset($reservationInfo)){
				$colorID = $this->clean($reservationInfo["color_type_id"]);
			}
			else{
				
			}		
			
			foreach ($colorCodes as $colorCode){
				$codeID = $this->clean($colorCode["id"]);
				$codeName = $this->clean($colorCode["name"]);
				$selected = "";
				if ($codeID == $colorID ){
					$selected = "selected=\"selected\"";
				}
				?>
				<OPTION value="<?php echo  $codeID ?>" <?php echo  $selected ?>> <?php echo  $codeName?> </OPTION>
				<?php 
			}
			?>
			</select>
			</div>
		</div>		
		
		<?php if ($canEditReservation){
			?>
			<div class="col-xs-5 col-xs-offset-7">
		<?php
		}else{
		?>
		<div class="col-xs-12">
		        <?php } 
                        if ($canEditReservation){
				?>	
				<input type="submit" class="btn btn-primary" value="<?php echo CoreTranslator::Save($lang)?>" />
				<?php if (isset($reservationBase)){?>
                                <button type="button" onclick="ConfirmDelete();" class="btn btn-danger"><?php echo CoreTranslator::Delete($lang) ?></button>
		        <?php }} ?>
				<button type="button" class="btn btn-default" onclick="location.href='calendar/book'"><?php echo CoreTranslator::Cancel($lang)?></button>
		</div>
      
      </div>
</div>

<?php include "Framework/timepicker_script.php"?>

<script>
document.getElementById('use_package').onchange = function() {
document.getElementById('package_div').style.display = this.checked ? 'block' : 'none';
document.getElementById('resa_time_div').style.display = ! this.checked ? 'block' : 'none';

};
</script>

<?php endblock();

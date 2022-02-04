<?php 

require_once 'Modules/booking/Model/BkCalSupInfo.php';

function bookday($id_space, $size_bloc_resa, $date_unix, $day_begin, $day_end, $calEntries, $isUserAuthorizedToBook, $isDayAvailable, $agendaStyle, $resourceID = -1){
	
        $modelSpace = new CoreSpace();
        $user_space_role = $modelSpace->getUserSpaceRole($id_space, $_SESSION["id_user"]);
    
	//if ($resourceID < 0){
	//	$resourceID = $_SESSION["bk_id_resource"];
	//}
	$dateString = date("Y-m-d", $date_unix);
	$modelBookingSupplemetary = new BkCalSupInfo();
	
	if ($size_bloc_resa == 900){
		// resa
		$caseTimeBegin = $date_unix + $day_begin*3600 - 900;
		$caseTimeEnd = $date_unix + $day_begin*3600;
		$caseTimeLength = 900;
		
		$modelBookingSetting = new BkBookingSettings();
		$leftBlocks = ($day_end*3600 - $day_begin*3600)/900;
		$lineColorId = 0;
		for ($h = $day_begin ; $h < $day_end ; $h = $h+0.25){
			$caseTimeBegin = $date_unix + $h*3600;
			$caseTimeEnd = $date_unix + $h*3600 +900;
				
			$foundStartEntry = false;
			
			foreach ($calEntries as $calEntry){
				
				if($h == $day_begin &&  $calEntry['start_time']<$caseTimeBegin){
				
					if ( $calEntry['end_time'] >= $caseTimeBegin){
				
						$foundStartEntry = true;
						$blocNumber = ($calEntry['end_time'] - $caseTimeBegin)/($caseTimeLength);
						$blocNumber = round($blocNumber); if ($blocNumber < 1){$blocNumber=1;}
				
						if ($leftBlocks <= $blocNumber){
							$blocNumber = $leftBlocks;
						}
						$leftBlocks -= $blocNumber;
						$lineColorId += $blocNumber;
							
						$pixelHeight = $blocNumber*$agendaStyle["line_height"];
				
						$shortDescription = $calEntry['short_description'];
						
						$text = "";
						if ($blocNumber <= 2){
							$text = $modelBookingSetting->getSummary($id_space, $calEntry["recipient_fullname"], $calEntry['phone'], $shortDescription, $calEntry['full_description'], true);
							$text .= $modelBookingSupplemetary->getSummary($id_space ,$calEntry["id"]);
							//$text = "<b>User: </b>". $calEntry["recipient_fullname"] . ", <b>Phone:</b>".$calEntry['phone']. ", <b>Desc:</b> " .$calEntry['short_description']."";
						}
						else{
							$text = $modelBookingSetting->getSummary($id_space, $calEntry["recipient_fullname"], $calEntry['phone'], $shortDescription, $calEntry['full_description'], false);
							$text .= $modelBookingSupplemetary->getSummary($id_space ,$calEntry["id"]);
							//$text = $text = "<b>User: </b>". $calEntry["recipient_fullname"] . ", </br><b>Phone:</b>".$calEntry['phone']. ", </br><b>Desc:</b> " .$calEntry['short_description']."";
						}
						if($text === '') {
							$text = '#'.$calEntry['id'];
						}
						$linkAdress = "bookingeditreservation/".$id_space ."/r_" . $calEntry['id'];
						?>
						<div class="text-center" id="tcellResa" style="height:<?php echo $pixelHeight?>px; background-color:<?php echo $calEntry['color_bg']?>;">
							<a class="text-center" style="color:<?php echo $calEntry["color_text"]?>; font-size: <?php echo $agendaStyle["resa_font_size"] ?>px;" href="<?php echo $linkAdress?>"><?php echo $text?></a>
						</div>
						<?php
						$h+= $blocNumber*0.25 - 0.25;								
					}
				}
								
				if ($calEntry['start_time'] >= $caseTimeBegin && $calEntry['start_time'] < $caseTimeEnd){
					// there is an entry in this half time
					$foundStartEntry = true;
					$blocNumber = ($calEntry['end_time'] - $calEntry['start_time'])/($caseTimeLength);
					$blocNumber = round($blocNumber); if ($blocNumber < 1){$blocNumber=1;}
					
					if ($leftBlocks <= $blocNumber){
						$blocNumber = $leftBlocks; 
					}
					$leftBlocks -= $blocNumber; 
					$lineColorId += $blocNumber;
					//echo "leftBlocks = " . $leftBlocks . "</br>";
					
					$pixelHeight = $blocNumber*$agendaStyle["line_height"];
						
					$shortDescription = $calEntry['short_description'];
					
					$text = "";
					if ($blocNumber <= 2){
						$text = $modelBookingSetting->getSummary($id_space, $calEntry["recipient_fullname"], $calEntry['phone'], $shortDescription, $calEntry['full_description'], true);
						$text .= $modelBookingSupplemetary->getSummary($id_space ,$calEntry["id"]);
						//$text = "<b>User: </b>". $calEntry["recipient_fullname"] . ", <b>Phone:</b>".$calEntry['phone']. ", <b>Desc:</b> " .$calEntry['short_description']."";
					}
					else{
						$text = $modelBookingSetting->getSummary($id_space, $calEntry["recipient_fullname"], $calEntry['phone'], $shortDescription, $calEntry['full_description'], false);
						$text .= $modelBookingSupplemetary->getSummary($id_space ,$calEntry["id"]);
						//$text = $text = "<b>User: </b>". $calEntry["recipient_fullname"] . ", </br><b>Phone:</b>".$calEntry['phone']. ", </br><b>Desc:</b> " .$calEntry['short_description']."";
					}
					if($text === '') {
						$text = '#'.$calEntry['id'];
					}
					$linkAdress = "bookingeditreservation/". $id_space ."/r_" . $calEntry['id']; 
					?>
								<div class="text-center" id="tcellResa" style="height: <?php echo $pixelHeight?>px; background-color:<?php echo $calEntry['color_bg']?>;">
								<a class="text-center" style="color:<?php echo $calEntry['color_text']?>; font-size: <?php echo $agendaStyle["resa_font_size"] ?>px;" href="<?php echo $linkAdress?>"><?php echo $text?></a>
								</div>
							<?php
							$h+= $blocNumber*0.25 - 0.25;
				}
					}
					if (!$foundStartEntry){
						$leftBlocks--;
						$lineColorId ++;
					?>
					 <?php
			    	while ($lineColorId > 2){
			    		$lineColorId -= 2;
			    	}
			   	    $bgColor = "#fff";
			   		if ($lineColorId > 1){
			    		$bgColor = "#e1e1e1";
			    	}
			    	?>
						<div class="text-center" id="tcell" style="height: <?php echo $agendaStyle["line_height"]?>px; background-color: <?php echo $bgColor?>;">
						<?php if ($isDayAvailable){
						?>
							<?php if ($isUserAuthorizedToBook){		
								$h2 = str_replace(".", "-", $h);
								$he = explode("-", $h2);
								if (count($he) == 1){$he[1] = "00";}
								if ($he[1] == "25"){$he[1] = "15";}
								if ($he[1] == "50"){$he[1] = "30";}
								if ($he[1] == "5"){$he[1] = "30";}
								if ($he[1] == "75"){$he[1] = "45";}
								if ($he[0] < 10){$he[0] = "0". $he[0];}
								$hed = $he[0] . "-" .$he[1];
								if( $user_space_role >=CoreSpace::$MANAGER  || $date_unix > time() || ( date("Y-m-d", $date_unix) == date("Y-m-d", time()) &&  $hed > date("H-m", time()) )){
									$linkAdress = "bookingeditreservation/". $id_space ."/t_" . $dateString."_".$hed."_".$resourceID;
							?>
							<a class="glyphicon glyphicon-plus" href="<?php echo $linkAdress?>"></a>
							<?php }
							}
						}
					?>
						</div>
					<?php 
			}	
		}
	}
	elseif ($size_bloc_resa == 1800){
		
		//echo "case 1800 <br/>";
		// resa
		$caseTimeBegin = $date_unix + $day_begin*3600 - 1800;
		$caseTimeEnd = $date_unix + $day_begin*3600;
		$caseTimeLength = 1800;

		$leftBlocks = ($day_end*3600 - $day_begin*3600)/1800;
		$modelBookingSetting = new BkBookingSettings();
		$lineColorId = 0;
		for ($h = $day_begin ; $h < $day_end ; $h = $h+0.5){
			
			$caseTimeBegin = $date_unix + $h*3600;
			$caseTimeEnd = $date_unix + $h*3600 +1800;
			
			$foundStartEntry = false;
			foreach ($calEntries as $calEntry){
				
				if($h == $day_begin &&  $calEntry['start_time']<$caseTimeBegin){
						
					if ( $calEntry['end_time'] >= $caseTimeBegin ){
						//echo "should not enter here <br/>";
				
						$foundStartEntry = true;
						$blocNumber = ($calEntry['end_time'] - $caseTimeBegin)/($caseTimeLength);
						$blocNumber = round($blocNumber); if ($blocNumber < 1){$blocNumber=1;}
				
						if ($leftBlocks <= $blocNumber){
							$blocNumber = $leftBlocks;
						}
						$leftBlocks -= $blocNumber;
						$lineColorId += $blocNumber;
							
						$pixelHeight = $blocNumber*$agendaStyle["line_height"];
				
						$shortDescription = $calEntry['short_description'];
						
						$text = "";
						if ($blocNumber <= 2){
							$text = $modelBookingSetting->getSummary($id_space, $calEntry["recipient_fullname"], $calEntry['phone'], $shortDescription, $calEntry['full_description'], true);
							$text .= $modelBookingSupplemetary->getSummary($id_space ,$calEntry["id"]);
							//$text = "<b>User: </b>". $calEntry["recipient_fullname"] . ", <b>Phone:</b>".$calEntry['phone']. ", <b>Desc:</b> " .$calEntry['short_description']."";
						}
						else{
							$text = $modelBookingSetting->getSummary($id_space, $calEntry["recipient_fullname"], $calEntry['phone'], $shortDescription, $calEntry['full_description'], false);
							$text .= $modelBookingSupplemetary->getSummary($id_space ,$calEntry["id"]);
							//$text = $text = "<b>User: </b>". $calEntry["recipient_fullname"] . ", </br><b>Phone:</b>".$calEntry['phone']. ", </br><b>Desc:</b> " .$calEntry['short_description']."";
						}
						if($text === '') {
							$text = '#'.$calEntry['id'];
						}
						$linkAdress = "bookingeditreservation/". $id_space ."/r_" . $calEntry['id'];
						?>
						<div class="text-center" id="tcellResa" style="height: <?php echo $pixelHeight?>px; background-color:<?php echo $calEntry['color_bg']?>;">
							
							<a class="text-center" style="color:<?php echo $calEntry["color_text"]?>; font-size: <?php echo $agendaStyle["resa_font_size"] ?>px;" href=<?php echo $linkAdress?>><?php echo $text?></a>
						</div>
						<?php
						$h+= $blocNumber*0.5 - 0.5;								
					}
				}
				
				if ($calEntry['start_time'] >= $caseTimeBegin && $calEntry['start_time'] < $caseTimeEnd){
					// there is an entry in this half time
					$foundStartEntry = true;
					$blocNumber = ($calEntry['end_time'] - $calEntry['start_time'])/($caseTimeLength);
					$blocNumber = round($blocNumber); if ($blocNumber < 1){$blocNumber=1;}
					
					if ($leftBlocks <= $blocNumber){
						$blocNumber = $leftBlocks;
					}
					$leftBlocks -= $blocNumber;
					$lineColorId += $blocNumber;
					
					$pixelHeight = $blocNumber*$agendaStyle["line_height"];
					
					$shortDescription = $calEntry['short_description'];
					
					$text = "";
					if ($blocNumber <= 2){
						$text = $modelBookingSetting->getSummary($id_space, $calEntry["recipient_fullname"], $calEntry['phone'], $shortDescription, $calEntry['full_description'], true);
						$text .= $modelBookingSupplemetary->getSummary($id_space ,$calEntry["id"]);
						//$text = "<b>User: </b>". $calEntry["recipient_fullname"] . ", <b>Phone:</b>".$calEntry['phone']. ", <b>Desc:</b> " .$calEntry['short_description']."";
					}
					else{
						$text = $modelBookingSetting->getSummary($id_space, $calEntry["recipient_fullname"], $calEntry['phone'], $shortDescription, $calEntry['full_description'], false);
						$text .= $modelBookingSupplemetary->getSummary($id_space, $calEntry["id"]);
						//$text = $text = "<b>User: </b>". $calEntry["recipient_fullname"] . ", </br><b>Phone:</b>".$calEntry['phone']. ", </br><b>Desc:</b> " .$calEntry['short_description']."";
					}
					if($text === '') {
						$text = '#'.$calEntry['id'];
					}
					$linkAdress = "bookingeditreservation/". $id_space ."/r_" . $calEntry['id'];
					?>
						<div class="text-center" id="tcellResa" style="height: <?php echo $pixelHeight?>px; background-color:<?php echo $calEntry['color_bg']?>;">
						<a class="text-center" style="color:<?php echo $calEntry["color_text"]?>;  font-size: <?php echo $agendaStyle["resa_font_size"] ?>px;" href="<?php echo $linkAdress?>"><?php echo $text?></a>
						</div>
					<?php
					$h+= $blocNumber*0.5 - 0.5;
				}
			}
			if (!$foundStartEntry){
				$leftBlocks--;
				$lineColorId++;
			?>
			   
			    <?php 
			    while ($lineColorId > 4){
			    	$lineColorId -= 4;
			    }
			    $bgColor = "#fff";
			    if ($lineColorId > 2){
			    	$bgColor = "#e1e1e1";
			    }
			    ?>
				<div class="text-center" id="tcell" style="height: <?php echo $agendaStyle["line_height"]?>px; background-color: <?php echo $bgColor?>;"> 
				<?php if ($isDayAvailable){?>
				<?php if ($isUserAuthorizedToBook){
					$h2 = str_replace(".", "-", $h);
					$he = explode("-", $h2);
					if (count($he) == 1){$he[1] = "00";}
					if ($he[1] == "5"){$he[1] = "30";}
					if ($he[0] < 10){$he[0] = "0". $he[0];}
					$hed = $he[0] . "-" .$he[1];
					if( $user_space_role >=3  || $date_unix > time() || ( date("Y-m-d", $date_unix) == date("Y-m-d", time()) &&  $hed > date("H-m", time()) )){
						$linkAdress = "bookingeditreservation/". $id_space ."/t_" . $dateString."_".$hed."_".$resourceID;
						?>
						 <a class="glyphicon glyphicon-plus" href="<?php echo $linkAdress?>"></a>
				<?php }}}?>
				  </div>
				
			<?php 
			}	
		}
	}
	elseif ($size_bloc_resa == 3600){
		// resa
		$caseTimeBegin = $date_unix + $day_begin*3600 - 3600;
		$caseTimeEnd = $date_unix + $day_begin*3600;
		$caseTimeLength = 3600;

		$leftBlocks = ($day_end*3600 - $day_begin*3600)/3600;
		$modelBookingSetting = new BkBookingSettings();
		$lineColorId = 0;

		
		for ($h = $day_begin ; $h < $day_end ; $h = $h+1){
				
			$caseTimeBegin = $date_unix + $h*3600;
			$caseTimeEnd = $date_unix + $h*3600 +3600;
				
			$foundStartEntry = false;
			foreach ($calEntries as $calEntry){
				
				if($h == $day_begin &&  $calEntry['start_time']<$caseTimeBegin){
					
					if ( $calEntry['end_time'] >= $caseTimeBegin ){
						
						$foundStartEntry = true;
						$blocNumber = ($calEntry['end_time'] - $caseTimeBegin)/($caseTimeLength);
						$blocNumber = round($blocNumber); if ($blocNumber < 1){$blocNumber=1;}
						
						if ($leftBlocks <= $blocNumber){
							$blocNumber = $leftBlocks;
						}
						$leftBlocks -= $blocNumber;
						$lineColorId += $blocNumber;
							
						$pixelHeight = $blocNumber*$agendaStyle["line_height"];
						
						$shortDescription = $calEntry['short_description'];

						$text = "";
						if ($blocNumber <= 2){
							$text = $modelBookingSetting->getSummary($id_space, $calEntry["recipient_fullname"], $calEntry['phone'], $shortDescription, $calEntry['full_description'], true);
							$text .= $modelBookingSupplemetary->getSummary($id_space, $calEntry["id"]);
							//$text = "<b>User: </b>". $calEntry["recipient_fullname"] . ", <b>Phone:</b>".$calEntry['phone']. ", <b>Desc:</b> " .$calEntry['short_description']."";
						}
						else{
							$text = $modelBookingSetting->getSummary($id_space, $calEntry["recipient_fullname"], $calEntry['phone'], $shortDescription, $calEntry['full_description'], false);
							$text .= $modelBookingSupplemetary->getSummary($id_space ,$calEntry["id"]);
							//$text = $text = "<b>User: </b>". $calEntry["recipient_fullname"] . ", </br><b>Phone:</b>".$calEntry['phone']. ", </br><b>Desc:</b> " .$calEntry['short_description']."";
						}
						if($text === '') {
							$text = '#'.$calEntry['id'];
						}
						$linkAdress = "bookingeditreservation/". $id_space ."/r_" . $calEntry['id'];
						?>
						<div class="text-center" id="tcellResa" style="height: <?php echo $pixelHeight?>px; background-color:<?php echo $calEntry['color_bg']?>;">
							<a class="text-center" style="color:<?php echo $calEntry["color_text"]?>; font-size: <?php echo $agendaStyle["resa_font_size"] ?>px;" href="<?php echo $linkAdress?>"><?php echo $text?></a>
						</div>
						<?php
						$h+= $blocNumber*1 - 1;
											
					}
				}
				
				if ($calEntry['start_time'] >= $caseTimeBegin && $calEntry['start_time'] < $caseTimeEnd ){
					// there is an entry in this half time
					$foundStartEntry = true;
					$blocNumber = ($calEntry['end_time'] - $calEntry['start_time'])/($caseTimeLength);
					$blocNumber = round($blocNumber); if ($blocNumber < 1){$blocNumber=1;}
					
					if ($leftBlocks <= $blocNumber){
						$blocNumber = $leftBlocks;
					}
					$leftBlocks -= $blocNumber;
					$lineColorId += $blocNumber;
					
					$pixelHeight = $blocNumber*$agendaStyle["line_height"];
						
					$shortDescription = $calEntry['short_description'];

					$text = "";
					if ($blocNumber <= 2){
						$text = $modelBookingSetting->getSummary($id_space, $calEntry["recipient_fullname"], $calEntry['phone'], $shortDescription, $calEntry['full_description'], true);
						$text .= $modelBookingSupplemetary->getSummary($id_space, $calEntry["id"]);
						//$text = "<b>User: </b>". $calEntry["recipient_fullname"] . ", <b>Phone:</b>".$calEntry['phone']. ", <b>Desc:</b> " .$calEntry['short_description']."";
					}
					else{
						$text = $modelBookingSetting->getSummary($id_space, $calEntry["recipient_fullname"], $calEntry['phone'], $shortDescription, $calEntry['full_description'], false);
						$text .= $modelBookingSupplemetary->getSummary($id_space ,$calEntry["id"]);
						//$text = $text = "<b>User: </b>". $calEntry["recipient_fullname"] . ", </br><b>Phone:</b>".$calEntry['phone']. ", </br><b>Desc:</b> " .$calEntry['short_description']."";
					}
					if($text === '') {
						$text = '#'.$calEntry['id'];
					}
					$linkAdress = "bookingeditreservation/".$id_space ."/r_" . $calEntry['id'];
					?>
								<div class="text-center" id="tcellResa" style="height: <?php echo $pixelHeight?>px; background-color:<?php echo $calEntry['color_bg']?>;">
								<a class="text-center" style="color:<?php echo $calEntry["color_text"]?>; font-size: <?php echo $agendaStyle["resa_font_size"] ?>px;" href="<?php echo $linkAdress?>"><?php echo $text?></a>
								</div>
							<?php
							$h+= $blocNumber*1 - 1;
						}
					}
					if (!$foundStartEntry){
						$leftBlocks--;
						$lineColorId++;
					?>
					
					 <?php 
			    	//echo "lineColorId = " . $lineColorId . "<br>";
			   		 while ($lineColorId > 2){
			    		$lineColorId -= 2;
			    	}
			    	$bgColor = "#fff";
			    	if ($lineColorId > 1){
			    		$bgColor = "#e1e1e1";
			    	}
			    	?>
					
						<div class="text-center" id="tcell" style="height: <?php echo $agendaStyle["line_height"]?>px; background-color: <?php echo $bgColor?>;">
						<?php if ($isDayAvailable){?>
							<?php if ($isUserAuthorizedToBook){
								$h2 = str_replace(".", "-", $h);
								$he = explode("-", $h2);
								if (count($he) == 1){$he[1] = "00";}
								if ($he[1] == "25"){$he[1] = "15";}
								if ($he[1] == "50"){$he[1] = "30";}
								if ($he[1] == "75"){$he[1] = "45";}
								if ($he[0] < 10){$he[0] = "0". $he[0];}
								$hed = $he[0] . "-" .$he[1];
								if( $user_space_role >=3  || $date_unix > time() || ( date("Y-m-d", $date_unix) == date("Y-m-d", time()) &&  $hed > date("H-m", time()) )){
									$linkAdress = "bookingeditreservation/".$id_space ."/t_" . $dateString."_".$h2."_".$resourceID;
									?>
								<a class="glyphicon glyphicon-plus" href="<?php echo $linkAdress?>"></a>
								<?php
								}
							}
						}?>
						</div>
					<?php 
					}	

				}
	}
}



function compute($id_space, $size_bloc_resa, $date_unix, $day_begin, $day_end, $calEntries, $isUserAuthorizedToBook, $isDayAvailable, $agendaStyle, $resourceID = -1){
	
	$modelSpace = new CoreSpace();
	$user_space_role = $modelSpace->getUserSpaceRole($id_space, $_SESSION["id_user"]);

	$dateString = date("Y-m-d", $date_unix);
	$modelBookingSupplemetary = new BkCalSupInfo();
	$modelBookingSetting = new BkBookingSettings();


	$calRows = [];

	$caseTimeLength = 3600;
	if ($size_bloc_resa == 900){
		$caseTimeLength = 900;
	} else if($size_bloc_resa == 1800) {
		$caseTimeLength = 1800;
	}

	foreach ($calEntries as $c => $calEntry){

		$caseTimeBegin = $date_unix + $day_begin*3600;
		$caseTimeEnd = $date_unix + $day_end*3600;
		if($calEntry['start_time'] < $caseTimeBegin) {
			$calEntry['start_time'] = $caseTimeBegin;
		}
		if($calEntry['end_time'] > $caseTimeEnd) {
			$calEntry['end_time'] = $caseTimeEnd;
		}
		$calLen = $calEntry['end_time'] - $calEntry['start_time'];
		$calEntry['expand'] = false;
		$cal = [$calEntry];
		if($calLen > $size_bloc_resa){
			$elts = $calLen / $size_bloc_resa;
			$cal = [];
			$end = $calEntry['end_time'];
			//$curHour = date('G', $calEntry['start_time']);
			for($i=0;$i<$elts;$i++) {
				$calEntry['end_time'] = $calEntry['start_time'] + $size_bloc_resa;
				if($calEntry['end_time'] > $end) {
					$calEntry['end_time'] = $end;
				}
				$calEntry['expand'] = true;
				$cal[] = $calEntry;
				$calEntry['start_time'] += $size_bloc_resa;
			}
		}
		foreach($cal as $c) {
			$blocNumber = ($c['end_time'] - $c['start_time'])/($caseTimeLength);
			$curHour = date('G', $c['start_time']);

			$pixelHeight = $blocNumber*$agendaStyle["line_height"];
			$shortDescription = $c['short_description'];
			$text = $modelBookingSetting->getSummary($id_space, $c["recipient_fullname"], $c['phone'], $shortDescription, $c['full_description'], true);
			$text .= $modelBookingSupplemetary->getSummary($id_space ,$calEntry["id"]);
			if($text === '') {
				$text = '#'.$c['id'];
			}
			$linkAdress = "bookingeditreservation/". $id_space ."/r_" . $c['id'];
			$c['text'] = $text;
			$c['pixelHeight'] = $pixelHeight;
			$c['link'] = $linkAdress;
			$c['hstart'] = date('H:i', $c['start_time']);
			$c['hend'] = date('H:i', $c['end_time']);
			$last = 0;
			if(key_exists($curHour, $calRows) && key_exists('entries', $calRows[$curHour])) {
				$last=count($calRows[$curHour]['entries']);
			}
			if($last > 0 && $calRows[$curHour]['entries'][$last-1]['id'] == $c['id']) {
				// same hour and same element, merge them
				$calRows[$curHour]['entries'][$last-1]['hend'] = $c['hend'];
				$calRows[$curHour]['entries'][$last-1]['end_time'] = $c['end_time'];
			} else {
				$calRows[$curHour]['entries'][] = $c;
			}
		}
	}

	for($i=$day_begin;$i<$day_end;$i++) {
		if(key_exists($i, $calRows)) {
			$calRows[$i]['plus'] = null;
		} else { 
			$calRows[$i] = ['entries' => [], 'plus' => null];
		}
	}

	foreach ($calRows as $h => $value) {
		$total=0;
		foreach ($value['entries'] as $c) {
			$total += $c['end_time'] - $c['start_time'];
		}
		if($total >= 3600) {
			continue; // hour is full
		}
		if ($isDayAvailable && $isUserAuthorizedToBook){
			$h2 = str_replace(".", "-", $h);
			$he = explode("-", $h2);
			if($caseTimeLength == 900) {
				if (count($he) == 1){$he[1] = "00";}
				if ($he[1] == "25"){$he[1] = "15";}
				if ($he[1] == "50"){$he[1] = "30";}
				if ($he[1] == "5"){$he[1] = "30";}
				if ($he[1] == "75"){$he[1] = "45";}
				if ($he[0] < 10){$he[0] = "0". $he[0];}
			} else if($caseTimeLength == 1800){
				if (count($he) == 1){$he[1] = "00";}
				if ($he[1] == "5"){$he[1] = "30";}
				if ($he[0] < 10){$he[0] = "0". $he[0];}
			} else {
				if (count($he) == 1){$he[1] = "00";}
				if ($he[1] == "25"){$he[1] = "15";}
				if ($he[1] == "50"){$he[1] = "30";}
				if ($he[1] == "75"){$he[1] = "45";}
				if ($he[0] < 10){$he[0] = "0". $he[0];}
			}
			$hed = $he[0] . "-" .$he[1];
			if( $user_space_role >=CoreSpace::$MANAGER  || $date_unix > time() || ( date("Y-m-d", $date_unix) == date("Y-m-d", time()) &&  $hed > date("H-m", time()) )){
				$linkAdress = "bookingeditreservation/". $id_space ."/t_" . $dateString."_".$hed."_".$resourceID;
				$calRows[$h]['plus'] = $linkAdress;
			}
		}
	}

return $calRows;
}
?>

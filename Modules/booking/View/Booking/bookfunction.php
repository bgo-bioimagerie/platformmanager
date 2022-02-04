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
	$step = 1;
	$lineColorStep = 2;
	if ($size_bloc_resa == 900){
		$caseTimeLength = 900;
		$step = 0.25;
		$lineColorStep = 2;
	} else if($size_bloc_resa == 1800) {
		$caseTimeLength = 1800;
		$step = 0.5;
		$lineColorStep = 4;
	}

	foreach ($calEntries as $c => $calEntry){
		$calHour = date('G', $calEntry['start_time']);
		$calDay = date('j', $calEntry['start_time']);
		$calDayOfWeek = date('N', $calEntry['start_time']);

		$caseTimeBegin = $date_unix + $day_begin*3600;
		$caseTimeEnd = $date_unix + $day_end*3600;
		if($calEntry['start_time'] < $caseTimeBegin) {
			$calEntry['start_time'] = $caseTimeBegin;
		}
		if($calEntry['end_time'] > $caseTimeEnd) {
			$calEntry['end_time'] = $caseTimeEnd;
		}
		$calLen = $calEntry['end_time'] - $calEntry['start_time'];
		$cal = [$calEntry];
		if($calLen > 3600){
			$elts = $calLen / 3600;
			$cal = [];
			//$start =  $calEntry['start_time'];
			$end =  $calEntry['start_time'];
			for($i=0;$i<$elts;$i++) {
				$calEntry['end_time'] = $calEntry['start_time'] + 3600;
				if($calEntry['end_time'] > $end) {
					$calEntry['end_time'] = $end;
				}
				$cal[] = $calEntry;
				$calEntry['start_time'] += 3600;
			}
		}
		foreach($cal as $c) {
			Configuration::getLogger()->error('?????', ['start' => date('H:i', $c['start_time']), 'end' => date('H:i', $c['end_time'])]);
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
			$c['hstart'] = date('i', $calEntry['start_time']);
			$c['hend'] = date('i', $calEntry['end_time']);
			$calRows[$curHour]['entries'][] = $c;
		}
	}

	Configuration::getLogger()->error('?????????? CAL', ['b' => $day_begin, 'e' => $day_end]);
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












	$leftBlocks = ($day_end*3600 - $day_begin*3600)/$caseTimeLength;
	$lineColorId = 0;
	$i=0;

	/*for ($h = $day_begin ; $h < $day_end ; $h = $h+$step){
		$curHour = str_replace(".", "-", $h);
		$calRows[$curHour[0]] = ['entries' => []];

		if($i > 20) {
			break;
		}
		$i++;
			
		$caseTimeBegin = $date_unix + $h*3600;
		$caseTimeEnd = $date_unix + $h*3600 +$caseTimeLength;
			
		$foundStartEntry = false;
		
		foreach ($calEntries as $c => $calEntry){
			Configuration::getLogger()->error('?????', ['d' => date('Y-m-d H:i', $calEntry['start_time']), 'h' => $h]);
			$calHour = date('G', $calEntry['start_time']);
			$calDay = date('j', $calEntry['start_time']);
			$calDayOfWeek = date('N', $calEntry['start_time']);
			
			
			if($h == $day_begin &&  $calEntry['start_time']<$caseTimeBegin && $calEntry['end_time'] >= $caseTimeBegin){
				$foundStartEntry = true;
				$blocNumber = ($calEntry['end_time'] - $caseTimeBegin)/($caseTimeLength);
			}
							
			if ($calEntry['start_time'] >= $caseTimeBegin && $calEntry['start_time'] < $caseTimeEnd){
				// there is an entry in this half time
				$foundStartEntry = true;
				$blocNumber = ($calEntry['end_time'] - $calEntry['start_time'])/($caseTimeLength);
			}

			if (!$foundStartEntry){
				$leftBlocks--;
				$lineColorId ++;
			
			
				while ($lineColorId > $lineColorStep){
					$lineColorId -= $lineColorStep;
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
						$calEntries[$c]['link'] = $linkAdress;
						$calRows[$curHour]['entries'][] = $calEntries[$c];
					}
				}		
			} else {
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
				}
				else{
					$text = $modelBookingSetting->getSummary($id_space, $calEntry["recipient_fullname"], $calEntry['phone'], $shortDescription, $calEntry['full_description'], false);
					$text .= $modelBookingSupplemetary->getSummary($id_space ,$calEntry["id"]);
				}
				if($text === '') {
					$text = '#'.$calEntry['id'];
				}
				$linkAdress = "bookingeditreservation/". $id_space ."/r_" . $calEntry['id'];
				$calEntries[$c]['text'] = $text;
				$calEntries[$c]['pixelHeight'] = $pixelHeight;
				$calEntries[$c]['link'] = $linkAdress;
				$calRows[$curHour]['entries'][] = $calEntries[$c];

				$h+= $blocNumber*$step - $step;
			}
			Configuration::getLogger()->error('??????',['block' => $blocNumber, "s" => $step, "h" => $h, "l" => $leftBlocks]);
		}
	}*/

return $calRows;
}
?>

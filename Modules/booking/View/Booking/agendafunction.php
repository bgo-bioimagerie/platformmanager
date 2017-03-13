<?php

function drawAgenda($id_space, $lang, $mois, $annee, $entries, $resourceBase, $agendaStyle){
	
	$mois_fr = Array("", "Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août","Septembre", "Octobre", "Novembre", "Décembre");
	
	
	$l_day=date("t",mktime(0,0,0,$mois,1,$annee));
	$x=date("N", mktime(0, 0, 0, $mois,1 , $annee));
	$y=date("N", mktime(0, 0, 0, $mois,$l_day , $annee));
	?>
	
	
	<!-- <div style="min-width:900px;">  -->
	<div class="col-xs-12">

	<!-- 
	<caption><?php echo  $mois_fr[$mois] . " " . $annee ?></caption>
	 -->
	 
	<table class="tableau">
	<caption>
	
	<div class="col-md-3" style="text-align: left;">
            <div class="btn-group" role="group" aria-label="...">
		<button type="button" onclick="location.href='bookingmonth/<?php echo $id_space ?>/daymonthbefore'" class="btn btn-default"> &lt; </button>
		<button type="button" onclick="location.href='bookingmonth/<?php echo $id_space ?>/daymonthafter'" class="btn btn-default"> > </button>
		<button type="button" onclick="location.href='bookingmonth/<?php echo $id_space ?>/thisMonth'" class="btn btn-default"><?php echo BookingTranslator::This_month($lang) ?> </button>
            </div>
        </div>
	<div class="col-md-3">
		<p ><strong> <?php echo  $mois_fr[$mois] . " " . $annee ?></strong></p>
		
		<p ><strong> <?php echo  $resourceBase["name"] ?></strong></p>
	</div>
	<div class="col-md-6" style="text-align: right;">
            <div class="btn-group" role="group" aria-label="...">
                <button type="button" onclick="location.href='bookingday/<?php echo $id_space ?>'" class="btn btn-default"><?php echo BookingTranslator::Day($lang) ?></button>
		<button type="button" onclick="location.href='bookingdayarea/<?php echo $id_space ?>'" class="btn btn-default"><?php echo  BookingTranslator::Day_Area($lang) ?></button>
		<button type="button" onclick="location.href='bookingweek/<?php echo $id_space ?>'" class="btn btn-default"><?php echo  BookingTranslator::Week($lang) ?></button>
		<button type="button" onclick="location.href='bookingweekarea/<?php echo $id_space ?>'" class="btn btn-default "><?php echo  BookingTranslator::Week_Area($lang) ?></button>
		<button type="button" class="btn btn-default active"><?php echo  BookingTranslator::Month($lang) ?></button>
            </div>
            </div>
	</div>
	</caption>
	<tr><th>Lun</th><th>Mar</th><th>Mer</th><th>Jeu</th><th>Ven</th><th>Sam</th><th>Dim</th></tr>
	<tr>
	<?php
	$case=0;
	if($x>1)
	for($i=1;$i<$x;$i++)
	{
		echo '<td class="desactive">&nbsp;</td>';
		$case++;
	}
	for($i=1;$i<($l_day+1);$i++)
	{
		$f=$y=date("N", mktime(0, 0, 0, $mois,$i , $annee));
		$da=$annee."-".$mois."-".$i;
		echo "<td>";
	
		?>
		<div style="text-align:right; font-size:12px; color:#999999;"> <?php echo  $i ?> </div>
		<?php 
		$found = false;
		$modelBookingSetting = new BkBookingSettings();
		//$moduleProject = new CoreProject();
		$ModulesManagerModel = new CoreMenu();
		$isProjectMode = false;//$ModulesManagerModel->getDataMenusUserType("projects");
		if ($isProjectMode > 0){
			$isProjectMode = true;
		}
		else{
			$isProjectMode = false;
		}
		foreach ($entries as $entry){
			if (date("d", $entry["start_time"]) == $i){
				$found = true;
				$shortDescription = $entry['short_description'];
				if ($isProjectMode){
					$shortDescription = $moduleProject->getProjectName($entry['short_description']);
				}
				?>
				<a href="bookingeditreservation/<?php echo $id_space ?>/r_<?php echo $entry["id"] ?>">
				
				<div style="background-color: <?php echo $entry['color_bg']?>; max-width:200px; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px;" >
				<p style="border-bottom: thin solid #e1e1e1; font-size:<?php echo $agendaStyle["resa_font_size"] ?>px; color:<?php echo $entry['color_text']?>;" >
				 <?php echo  date("H:i", $entry["start_time"]) . " - " . date("H:i", $entry["end_time"]) ?></p>
				 <?php $text = $modelBookingSetting->getSummary($entry["recipient_fullname"], $entry['phone'], $shortDescription, $entry['full_description'], true); ?>
				<p style="font-size:<?php echo $agendaStyle["resa_font_size"] ?>px; color:<?php echo $entry['color_text']?>;"><?php echo  $text ?></p>
				</div>
				</a>
				<?php
			}
		}
		if($found == false){
			?>
			<div style="height:45px;"> </div>
			<?php 
		}
		
		echo "</td>";
		$case++;
		if($case%7==0){
			echo "</tr><tr>";
                }
		
	}
	if($y!=7){
		for($i=$y;$i<7;$i++){
			echo '<td class="desactive">&nbsp;</td>';
		}
        }
	?></tr>
	</table>
        </div>
	<?php 
}

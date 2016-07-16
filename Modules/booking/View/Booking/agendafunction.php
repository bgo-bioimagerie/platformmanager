<?php

function drawAgenda($mois, $annee, $entries, $resourceBase){
	
	$lang = "en";
	if (isset($_SESSION["user_settings"]["language"])){
		$lang = $_SESSION["user_settings"]["language"];
	}
	
	$list_fer=array(7);//Liste pour les jours ferié; EX: $list_fer=array(7,1)==>tous les dimanches et les Lundi seront des jours fériers
	$list_spe=array('1986-10-31','2015-3-17','2009-9-23');//Mettez vos dates des evenements ; NB format(annee-m-j)
	$lien_redir="date_info.php";//Lien de redirection apres un clic sur une date, NB la date selectionner va etre ajouter à ce lien afin de la récuperer ultérieurement
	
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
		<button type="button" onclick="location.href='bookingmonth/daymonthbefore'" class="btn btn-default"> &lt; </button>
		<button type="button" onclick="location.href='bookingmonth/daymonthafter'" class="btn btn-default"> > </button>
		<button type="button" onclick="location.href='bookingmonth/thisMonth'" class="btn btn-default"><?php echo BookingTranslator::This_month($lang) ?> </button>
            </div>
        </div>
	<div class="col-md-4">
		<p ><strong> <?php echo  $mois_fr[$mois] . " " . $annee ?></strong></p>
		
		<p ><strong> <?php echo  $resourceBase["name"] ?></strong></p>
	</div>
	<div class="col-md-5" style="text-align: right;">
            <div class="btn-group" role="group" aria-label="...">
                <button type="button" onclick="location.href='bookingday'" class="btn btn-default"><?php echo BookingTranslator::Day($lang) ?></button>
		<button type="button" onclick="location.href='bookingdayarea'" class="btn btn-default"><?php echo  BookingTranslator::Day_Area($lang) ?></button>
		<button type="button" onclick="location.href='bookingweek'" class="btn btn-default"><?php echo  BookingTranslator::Week($lang) ?></button>
		<button type="button" onclick="location.href='bookingweekarea'" class="btn btn-default "><?php echo  BookingTranslator::Week_Area($lang) ?></button>
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
		$moduleProject = new CoreProject();
		$ModulesManagerModel = new CoreMenu();
		$isProjectMode = $ModulesManagerModel->getDataMenusUserType("projects");
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
				<a href="calendar/editreservation/r_<?php echo $entry["id"] ?>">
				
				<div style="background-color: <?php echo $entry['color_bg']?>; max-width:200px; -webkit-border-radius: 5px; -moz-border-radius: 5px; border-radius: 5px;" >
				<p style="border-bottom: thin solid #e1e1e1; font-size:12px; color:<?php echo $entry['color_text']?>;" >
				 <?php echo  date("H:i", $entry["start_time"]) . " - " . date("H:i", $entry["end_time"]) ?></p>
				 <?php $text = $modelBookingSetting->getSummary($entry["recipient_fullname"], $entry['phone'], $shortDescription, $entry['full_description'], true); ?>
				<p style="font-size:12px; color:<?php echo $entry['color_text']?>;"><?php echo  $text ?></p>
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

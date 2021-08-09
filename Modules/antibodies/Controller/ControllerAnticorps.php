<?php
require_once 'Framework/Controller.php';
require_once 'Modules/core/Controller/ControllerSecureNav.php';
require_once 'Modules/anticorps/Model/AcInstall.php';
require_once 'Modules/anticorps/Model/Anticorps.php';
require_once 'Modules/anticorps/Model/Espece.php';
require_once 'Modules/anticorps/Model/Organe.php';
require_once 'Modules/anticorps/Model/Prelevement.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreTranslator.php';

class ControllerAnticorps extends ControllerSecureNav {
	
	// Affiche la liste de tous les billets du blog
	public function index($id_space) {
		
		if ( isset($_SESSION["ac_advSearch"])){
			$this->advsearchquery($id_space, "index");
			//echo "go to adv search";
			return;
		}
		
		// get sort action
		$sortentry = "no_h2p2";
		if ($this->request->isParameterNotEmpty('actionid')){
			$sortentry = $this->request->getParameter("actionid");
		}
			
		// get the user list
		$anticorpsModel = new Anticorps();
		$anticorpsArray = $anticorpsModel->getAnticorpsInfo($id_space, $sortentry);
		
		$modelstatus = new Status();
		$status = $modelstatus->getStatus($id_space);
		
		$navBar = $this->navBar();
		$this->generateView ( array (
				'navBar' => $navBar, 'anticorpsArray' => $anticorpsArray, 'status' => $status
		) );
	
	}
	
	public function anticorpscsv($id_space){
		// database query
		$anticorpsModel = new Anticorps();
		$anticorpsArray = $anticorpsModel->getAnticorpsInfo($id_space, "no_h2p2");
		
		$modelstatus = new Status();
		$status = $modelstatus->getStatus($id_space);
		
		$lang = "En";
		if (isset($_SESSION["user_settings"]["language"])){
			$lang = $_SESSION["user_settings"]["language"];
		}
		
		// make csv file
		$data = " Anticorps; ; ; ; ; ; ; ; ; Protocole; ; Tissus; ; ; ; ; ; Propriétaire; ; ;  \r\n";
		$data .= " No; Nom; St; Fournisseur; Source; Référence; Clone; lot; Isotype; proto; Acl dil; commentaire; espèce; organe; statut; ref. bloc; prélèvement; Nom; disponibilité; Date réception;  No Dossier \r\n";
		
		foreach ($anticorpsArray as $anticorps){
			
			$data .= $anticorps ['no_h2p2'] . " ; ";
			$data .= $anticorps ['nom'] . " ; ";
			$data .= $anticorps ['stockage'] . " ; "; 
			$data .= $anticorps ['fournisseur']. " ; ";
			$data .= $anticorps ['source']. " ; ";
			$data .= $anticorps ['reference']. " ; ";
			$data .= $anticorps ['clone']. " ; ";
			$data .= $anticorps ['lot']. " ; ";
			$data .= $anticorps ['isotype']. " ; ";

			// PROTOCOLE
			$tissus = $anticorps ['tissus'];
			$val = "";
			for( $i = 0 ; $i < count($tissus) ; ++$i){
				if($tissus[$i]['ref_protocol'] == "0"){
					$val .= "Manuel, ";
				}
				else{
					$val .= $tissus[$i]['ref_protocol'];
				}
			}
			$data .= $val. " ; ";
			
			$tissus = $anticorps ['tissus'];
			$val = "";
			for( $i = 0 ; $i < count($tissus) ; ++$i){
				$val = $val . " "
						. $tissus[$i]['dilution']
						. ", ";
			}
			$data .= $val. " ; ";
			
			// TISSUS
			$tissus = $anticorps ['tissus'];
			$val = "";
			for( $i = 0 ; $i < count($tissus) ; ++$i){
				$string = trim(preg_replace('/\s+/', ' ', $tissus[$i]['comment']));
				$val = $val . " "
					. $string
					. ", ";
			}
			$data .= $val . " ; ";

			$tissus = $anticorps ['tissus'];
			$val = "";
			for( $i = 0 ; $i < count($tissus) ; ++$i){
				$val = $val . " "  . $tissus[$i]['espece']
				. ", ";
			}
			$data .= $val. " ; ";
			
			$tissus = $anticorps ['tissus'];
			$val = "";
			for( $i = 0 ; $i < count($tissus) ; ++$i){
				$val = $val . " "
						. $tissus[$i]['organe']
						. ", ";
			}
			$data .= $val. " ; ";
			
			$tissus = $anticorps ['tissus'];
			$val = "";
			for( $i = 0 ; $i < count($tissus) ; ++$i){
			
				$statusTxt = "";
				foreach($status as $stat){
					if ($tissus[$i]['status'] == $stat["id"]){
						$statusTxt = $stat['nom'];
					}
				}
				$val = $val . " " . $statusTxt . ", ";
			}
			$data .= $val. " ; ";
			
			$tissus = $anticorps ['tissus'];
			$val = "";
			for( $i = 0 ; $i < count($tissus) ; ++$i){
				$val = $val . "" . $tissus[$i]['ref_bloc'] . ", ";
			}
			$data .= $val. " ; ";
			
			$tissus = $anticorps ['tissus'];
			$val = "";
			for( $i = 0 ; $i < count($tissus) ; ++$i){
				$val = $val . " " . $tissus[$i]['prelevement'] . ", ";
			}
			$data .= $val. " ; ";
			
			// OWNER
			$owner =  $anticorps ['proprietaire'];
			foreach ($owner as $ow){
				$name = $ow['name'] . " " . $ow['firstname'];
				$dispo = $ow['disponible'];
				if ($dispo == 1){$dispo = "disponible";}
				else if ($dispo == 2){$dispo = "épuisé";}
				else if ($dispo == 3){$dispo = "récupéré par équipe";}
				$date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
				$txt = $name;
				$data .= $txt. " ; ";
			}
			
			$owner =  $anticorps ['proprietaire'];
			foreach ($owner as $ow){
				$dispo = $ow['disponible'];
				if ($dispo == 1){$dispo = "disponible";}
				else if ($dispo == 2){$dispo = "épuisé";}
				else if ($dispo == 3){$dispo = "récupéré par équipe";}
				$date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
				$txt = $dispo;
			
				$data .= $txt. " , ";
			}
			$data .= ";";
			$owner =  $anticorps ['proprietaire'];
			foreach ($owner as $ow){
				$name = $ow['name'] . " " . $ow['firstname'];
				$dispo = $ow['disponible'];
				if ($dispo == 1){$dispo = "disponible";}
				else if ($dispo == 2){$dispo = "épuisé";}
				else if ($dispo == 3){$dispo = "récupéré par équipe";}
				$date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
				$txt = $date_recept;
			
				$data .= $txt. " , ";
			}
			$data .= ";";
			
			$owner =  $anticorps ['proprietaire'];
			foreach ($owner as $ow){
				$name = $ow['name'] . " " . $ow['firstname'];
				$dispo = $ow['disponible'];
				if ($dispo == 1){$dispo = "disponible";}
				else if ($dispo == 2){$dispo = "épuisé";}
				else if ($dispo == 3){$dispo = "récupéré par équipe";}
				$date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
				$txt = $ow['no_dossier'];
			
				$data .= $txt. " , ";
			}
			$data .= ";";
			$data .= "\r\n";
		}
		
		header("Content-Type: application/csv-tab-delimited-table");
		header("Content-disposition: filename=anticorps.csv");
		echo $data;
	}
	
	public function edit($id_space){

		// Lists for the form	
		// get isotypes list
		$modelIsotype = new Isotype();
		$isotypesList = $modelIsotype->getIsotypes($id_space);
	
		// get sources list
		$modelSource = new Source();
		$sourcesList = $modelSource->getSources($id_space);
                
		// get applications list
		$modelApp = new AcApplication();
		$applicationsList = $modelApp->getApplications($id_space);
		
		// get applications list
		$modelStaining = new AcStaining();
		$stainingsList = $modelStaining->getStainings($id_space);
                
		// get especes list
		$especesModel = new Espece($id_space);
		$especes = $especesModel->getEspeces($id_space, "nom");
		
		// get especes list
		$organesModel = new Organe();
		$organes = $organesModel->getOrganes($id_space, "nom");
		
		// get proto list
		$protoModel = new AcProtocol();
		$protocols = $protoModel->getProtocolsNo($id_space);
		
		// get users List
		$modelUser = new CoreUser();
		$users = $modelUser->getUsersSummary($id_space, 'name');
		
		
		// get prelevements list
		$modelPrelevement = new Prelevement();
		$prelevements = $modelPrelevement->getPrelevements($id_space, "nom"); 
		
		$modelstatus = new Status();
		$status = $modelstatus->getStatus($id_space);
			
		// get edit id
		$editID = "";
		if ($this->request->isParameterNotEmpty ( 'actionid' )) {
			$editID = $this->request->getParameter ( "actionid" );
		}
		
		$anticorps = array();
		$modelAnticorps = new Anticorps();
		if ($editID != ""){	
			$anticorps = $modelAnticorps->getAnticorpsFromId($id_space, $editID);
		}
		else{
			$anticorps = $modelAnticorps->getDefaultAnticorps();
		}
		
		$navBar = $this->navBar();
		$this->generateView ( array (
				'navBar' => $navBar,
				'isotypesList' => $isotypesList,
				'sourcesList' => $sourcesList,
				'anticorps' => $anticorps,
				'especes' => $especes,
				'organes' => $organes,
				'users' => $users,
				'protocols' => $protocols,
				'prelevements' => $prelevements,
				'status' => $status,
				'applicationsList' => $applicationsList,
				'stainingsList' => $stainingsList    
		) );
	}
	public function editquery($id_space){
		
		$lang = "En";
		if (isset($_SESSION["user_settings"]["language"])){
			$lang = $_SESSION["user_settings"]["language"];
		}
		
		// add in anticorps table
		$id = $this->request->getParameterNoException("id");
		$nom = $this->request->getParameter ("nom");
		$no_h2p2 = $this->request->getParameter ("no_h2p2");
		$reference = $this->request->getParameter ("reference");
		$clone = $this->request->getParameter ("clone");
		$fournisseur = $this->request->getParameter ("fournisseur");
		$lot = $this->request->getParameter ("lot");
		$id_isotype = $this->request->getParameter ("id_isotype");
		$id_source = $this->request->getParameter ("id_source");
		$stockage = $this->request->getParameter ("stockage");
		
		$id_proprietaire = $this->request->getParameter("id_proprietaire");
		$disponible = $this->request->getParameter ("disponible");
		$date_recept = $this->request->getParameter ("date_recept");
		$no_dossier = $this->request->getParameter ("no_dossier");
		
		$espece = $this->request->getParameter ("espece");
		$organe = $this->request->getParameter ("organe");
		$valide = $this->request->getParameter ("status");
		$ref_bloc = $this->request->getParameter ("ref_bloc");
		$prelevement = $this->request->getParameter ("prelevement");
		$dilution = $this->request->getParameter ("dilution");
		$temps_incubation = $this->request->getParameterNoException ("temps_incubation");
		$ref_protocol = $this->request->getParameter ("ref_protocol");
		$comment = $this->request->getParameter ("comment");
                
		$export_catalog = $this->request->getParameterNoException ("export_catalog");
		$id_application = $this->request->getParameter ("id_application");
		$id_staining = $this->request->getParameter ("id_staining");
		$image_desc = $this->request->getParameter ("image_desc");
		
                
		//print_r($export_catalog);
                //print_r($image_desc);
		
		$modelAnticorps = new Anticorps();
		$modelTissus = new Tissus();
		if ($id == ""){
			// add anticorps to table 
			$id = $modelAnticorps->addAnticorps($id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reference, $clone,
												$lot, $id_isotype, $stockage);
		}
		else{
			
			// update antibody
			$modelAnticorps->updateAnticorps($id, $id_space ,$nom, $no_h2p2, $fournisseur, $id_source, $reference, $clone,
					$lot, $id_isotype, $stockage);
			
			// remove all the owners
			$modelAnticorps->removeOwners($id_space, $id);
			
			// remove all the Tissus
			$modelTissus->removeTissus($id_space, $id);
			
		}
		
		// add the owner
		$i = -1;
		foreach ($id_proprietaire as $proprio){
			$i++;
			//echo "date proprio = " . $date_recept[$i];
			if ($proprio > 1 ){
				$date_r = CoreTranslator::dateToEn($date_recept[$i], $lang); 
				$modelAnticorps->addOwner($id_space, $proprio, $id, $date_r, $disponible[$i], $no_dossier[$i]);
			}
		}
		// add to the tissus table
		for ($i = 0 ; $i <  count($espece) ; $i++){		
			$temps_incubation = "";
			$modelTissus->addTissus($id_space, $id, $espece[$i], $organe[$i], $valide[$i], $ref_bloc[$i],
					$dilution[$i], $temps_incubation, $ref_protocol[$i], $prelevement[$i], $comment[$i]);
		}
                
                // add catalog informations
                if ($export_catalog == "on"){
                    $export_catalog = 1;
                }
                else{
                    $export_catalog = 0;
                }
                $modelAnticorps->setExportCatalog($id_space,$id, $export_catalog);
                $modelAnticorps->setApplicationStaining($id_space,$id, $id_staining, $id_application);
                $modelAnticorps->setImageDesc($id_space,$id, $image_desc);
                if ($_FILES["image_url"]["name"] != ""){
                    // download file
                    $filename =$this->downloadIllustration($id_space);
				
                    // set filename to database
                    $modelAnticorps->setImageUrl($id_space, $id, $filename);
		}
		    
	    // generate view
	    $this->redirect("anticorps", "index");
	    
	}
        
    protected function downloadIllustration($id_space){
		$target_dir = "data/antibodies/";
		$filename = $id_space."_".$_FILES["image_url"]["name"];
		$target_file = $target_dir . $filename;
		//echo "target file = " . $target_file . "<br/>";
		$uploadOk = 1;

		// Check file size
		if ($_FILES["image_url"]["size"] > 500000000) {
			throw PfmException("file too large.", 500);
			//$uploadOk = 0;
		}
                
		// Check if $uploadOk is set to 0 by an error
		if ($uploadOk == 0) {
			throw PfmException("there was an error uploading your file.", 500);
			// if everything is ok, try to upload file
		} else {
			if (move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_file)) {
				return  $filename;
			} else {
				throw PfmException("there was an error uploading your file.", 500);
			}
		}
	}
	
	
	public function searchquery($id_space){
		
		$lang = "En";
		if (isset($_SESSION["user_settings"]["language"])){
			$lang = $_SESSION["user_settings"]["language"];
		}
		
		$searchColumn = $this->request->getParameter ("searchColumn");
		$searchTxt = $this->request->getParameter ("searchTxt");
		
		$anticorpsArray = "";
		$anticorpsModel = new Anticorps();
		if($searchColumn == "0"){
			$anticorpsArray = $anticorpsModel->getAnticorpsInfo($id_space);
		}	
		else if($searchColumn == "Nom"){
			$anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "nom", $searchTxt);
		}
		else if($searchColumn == "No_h2p2"){
			$anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "no_h2p2", $searchTxt);
		}
		else if($searchColumn == "Fournisseur"){
			$anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "fournisseur", $searchTxt);
		}
		else if($searchColumn == "Source"){
			
			$modelSource = new Source();
			$st = $modelSource->getIdFromName($searchTxt, $id_space);
			$anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "id_source", $st);
		}
		else if($searchColumn == "Reference"){
			$anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "reference", $searchTxt);
		}
		else if($searchColumn == "Clone"){
			$anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "clone", $searchTxt);
		}
		else if($searchColumn == "lot"){
			$anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "lot", $searchTxt);
		}
		else if($searchColumn == "Isotype"){
			
			$modelIsotype = new Isotype();
			$st = $modelIsotype->getIdFromName($searchTxt, $id_space);
			$anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "id_isotype", $st);
		}
		else if($searchColumn == "Stockage"){
			$anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "stockage", $searchTxt);
		}
		else if($searchColumn == "dilution"){
			$anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch($id_space, "dilution", $searchTxt);
		}
		else if($searchColumn == "temps_incub"){
			$anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch($id_space, "temps_incubation", $searchTxt);
		}
		else if($searchColumn == "ref_proto"){
			$anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch($id_space, "ref_protocol", $searchTxt);
		}
		else if($searchColumn == "espece"){
			$modelEspece = new Espece();
			$id = $modelEspece->getIdFromName($searchTxt, $id_space);
			$anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch($id_space, "espece", $searchTxt);
		}
		else if($searchColumn == "organe"){
			//$modelOrgane = new Organe();
			//$id = $modelOrgane->getIdFromName($searchTxt);
			$anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch($id_space,"organe", $searchTxt);
		}
		else if($searchColumn == "valide"){
			$anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch($id_space,"valide", $searchTxt);
		}
		else if($searchColumn == "ref_bloc"){
			$anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch($id_space,"ref_bloc", $searchTxt);
		}
		else if($searchColumn == "nom_proprio"){
			$anticorpsArray = $anticorpsModel->getAnticorpsProprioSearch($id_space,"nom_proprio", $searchTxt);
		}
		else if($searchColumn == "disponibilite"){
			$anticorpsArray = $anticorpsModel->getAnticorpsProprioSearch($id_space,"disponibilite", $searchTxt);
		}
		else if($searchColumn == "date_recept"){
			$anticorpsArray = $anticorpsModel->getAnticorpsProprioSearch($id_space,"date_recept", CoreTranslator::dateToEn($searchTxt, $lang));
		}
		
		
		$modelstatus = new Status();
		$status = $modelstatus->getStatus($id_space);
		
		$navBar = $this->navBar();
		$this->generateView ( array (
				'navBar' => $navBar, 'anticorpsArray' => $anticorpsArray,
				'searchColumn' => $searchColumn, 'searchTxt' => $searchTxt,
				'status' => $status
		), "index" );

	}
	
	public function advsearchquery($id_space, $source = ""){
		
		
		if ($source == "index"){
			
			//print_r($_SESSION["ac_advSearch"]);
			//return;
			$searchName = $_SESSION["ac_advSearch"]["searchName"];
			$searchNoH2P2 = $_SESSION["ac_advSearch"]["searchNoH2P2"];
			$searchSource = $_SESSION["ac_advSearch"]["searchSource"];
			$searchCible = $_SESSION["ac_advSearch"]["searchCible"];
			$searchValide = $_SESSION["ac_advSearch"]["searchValide"];
			$searchResp = $_SESSION["ac_advSearch"]["searchResp"];
			
		}
		else{
			$searchName = $this->request->getParameterNoException("searchName");
			$searchNoH2P2 = $this->request->getParameterNoException ("searchNoH2P2");
			$searchSource = $this->request->getParameterNoException ("searchSource");
			$searchCible = $this->request->getParameterNoException ("searchCible");
			$searchValide = $this->request->getParameterNoException ("searchValide");
			$searchResp = $this->request->getParameterNoException ("searchResp");
		}
		
		$_SESSION["ac_advSearch"] = array(  "searchName" => $searchName,
											"searchNoH2P2" => $searchNoH2P2,
											"searchSource" => $searchSource,
											"searchCible" => $searchCible,
											"searchValide" => $searchValide,
											"searchResp" => $searchResp);
		
		$anticorpsModel = new Anticorps();
		$anticorpsArray = $anticorpsModel->searchAdv($id_space, $searchName, $searchNoH2P2, $searchSource, $searchCible, $searchValide, $searchResp);
		//$anticorpsArray = $anticorpsModel->getAnticorpsInfo("id");
		
		
		$modelstatus = new Status();
		$status = $modelstatus->getStatus($id_space);
		
		$navBar = $this->navBar();
		$this->generateView ( array (
				'navBar' => $navBar, 'anticorpsArray' => $anticorpsArray,
				'searchName' => $searchName, 
				'searchNoH2P2' => $searchNoH2P2, 
				'searchSource' => $searchSource, 
				'searchCible' => $searchCible, 
				'searchValide' => $searchValide, 
				'searchResp' => $searchResp,
				'status' => $status
		), "index" );
	}
	
	public function delete($id_space){
	
		// get source id
		$id = 0;
		if ($this->request->isParameterNotEmpty ( 'actionid' )) {
			$id = $this->request->getParameter ( "actionid" );
		}
	
		// get source info
		$anticorpsModel = new Anticorps();
		$source = $anticorpsModel->delete($id_space, $id);
	
		$this->redirect ( "anticorps" );
	}
	
}

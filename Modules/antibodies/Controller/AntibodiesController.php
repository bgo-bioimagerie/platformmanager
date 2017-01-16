<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/AntibodiesTranslator.php';
//require_once 'Modules/antibodies/Model/AcInstall.php';
require_once 'Modules/antibodies/Model/Anticorps.php';
require_once 'Modules/antibodies/Model/Espece.php';
require_once 'Modules/antibodies/Model/Status.php';
require_once 'Modules/antibodies/Model/Organe.php';
require_once 'Modules/antibodies/Model/Prelevement.php';
require_once 'Modules/antibodies/Model/AcProtocol.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class AntibodiesController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("antibodies");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $sortentry = "id") {

        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);

        if (isset($_SESSION["ac_advSearch"])) {
            $this->advsearchqueryAction($id_space, "index");
            //echo "go to adv search";
            return;
        }

        // get sort action
        if ($sortentry == "") {
            $sortentry = "no_h2p2";
        }

        // get the user list
        $anticorpsModel = new Anticorps();
        $anticorpsArray = $anticorpsModel->getAnticorpsInfo($sortentry);

        $modelstatus = new Status();
        $status = $modelstatus->getStatus();

        $this->render(array(
            'id_space' => $id_space, 'anticorpsArray' => $anticorpsArray,
            'status' => $status, 'lang' => $this->getLanguage()
        ));
    }

    public function anticorpscsv() {
        // database query
        $anticorpsModel = new Anticorps();
        $anticorpsArray = $anticorpsModel->getAnticorpsInfo("no_h2p2");

        $modelstatus = new Status();
        $status = $modelstatus->getStatus();

        $lang = $this->getLanguage();

        // make csv file
        $data = " Anticorps; ; ; ; ; ; ; ; ; Protocole; ; Tissus; ; ; ; ; ; Propriétaire; ; ;  \r\n";
        $data .= " No; Nom; St; Fournisseur; Source; Référence; Clone; lot; Isotype; proto; Acl dil; commentaire; espèce; organe; statut; ref. bloc; prélèvement; Nom; disponibilité; Date réception;  No Dossier \r\n";

        foreach ($anticorpsArray as $anticorps) {

            $data .= $anticorps ['no_h2p2'] . " ; ";
            $data .= $anticorps ['nom'] . " ; ";
            $data .= $anticorps ['stockage'] . " ; ";
            $data .= $anticorps ['fournisseur'] . " ; ";
            $data .= $anticorps ['source'] . " ; ";
            $data .= $anticorps ['reference'] . " ; ";
            $data .= $anticorps ['clone'] . " ; ";
            $data .= $anticorps ['lot'] . " ; ";
            $data .= $anticorps ['isotype'] . " ; ";

            // PROTOCOLE
            $tissus = $anticorps ['tissus'];
            $val = "";
            for ($i = 0; $i < count($tissus); ++$i) {
                if ($tissus[$i]['ref_protocol'] == "0") {
                    $val .= "Manuel, ";
                } else {
                    $val .= $tissus[$i]['ref_protocol'];
                }
            }
            $data .= $val . " ; ";

            $tissus = $anticorps ['tissus'];
            $val = "";
            for ($i = 0; $i < count($tissus); ++$i) {
                $val = $val . " "
                        . $tissus[$i]['dilution']
                        . ", ";
            }
            $data .= $val . " ; ";

            // TISSUS
            $tissus = $anticorps ['tissus'];
            $val = "";
            for ($i = 0; $i < count($tissus); ++$i) {
                $string = trim(preg_replace('/\s+/', ' ', $tissus[$i]['comment']));
                $val = $val . " "
                        . $string
                        . ", ";
            }
            $data .= $val . " ; ";

            $tissus = $anticorps ['tissus'];
            $val = "";
            for ($i = 0; $i < count($tissus); ++$i) {
                $val = $val . " " . $tissus[$i]['espece']
                        . ", ";
            }
            $data .= $val . " ; ";

            $tissus = $anticorps ['tissus'];
            $val = "";
            for ($i = 0; $i < count($tissus); ++$i) {
                $val = $val . " "
                        . $tissus[$i]['organe']
                        . ", ";
            }
            $data .= $val . " ; ";

            $tissus = $anticorps ['tissus'];
            $val = "";
            for ($i = 0; $i < count($tissus); ++$i) {

                $statusTxt = "";
                foreach ($status as $stat) {
                    if ($tissus[$i]['status'] == $stat["id"]) {
                        $statusTxt = $stat['nom'];
                    }
                }
                $val = $val . " " . $statusTxt . ", ";
            }
            $data .= $val . " ; ";

            $tissus = $anticorps ['tissus'];
            $val = "";
            for ($i = 0; $i < count($tissus); ++$i) {
                $val = $val . "" . $tissus[$i]['ref_bloc'] . ", ";
            }
            $data .= $val . " ; ";

            $tissus = $anticorps ['tissus'];
            $val = "";
            for ($i = 0; $i < count($tissus); ++$i) {
                $val = $val . " " . $tissus[$i]['prelevement'] . ", ";
            }
            $data .= $val . " ; ";

            // OWNER
            $owner = $anticorps ['proprietaire'];
            foreach ($owner as $ow) {
                $name = $ow['name'] . " " . $ow['firstname'];
                $dispo = $ow['disponible'];
                if ($dispo == 1) {
                    $dispo = "disponible";
                } else if ($dispo == 2) {
                    $dispo = "épuisé";
                } else if ($dispo == 3) {
                    $dispo = "récupéré par équipe";
                }
                $date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
                $txt = $name;
                $data .= $txt . " ; ";
            }

            $owner = $anticorps ['proprietaire'];
            foreach ($owner as $ow) {
                $dispo = $ow['disponible'];
                if ($dispo == 1) {
                    $dispo = "disponible";
                } else if ($dispo == 2) {
                    $dispo = "épuisé";
                } else if ($dispo == 3) {
                    $dispo = "récupéré par équipe";
                }
                $date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
                $txt = $dispo;

                $data .= $txt . " , ";
            }
            $data .= ";";
            $owner = $anticorps ['proprietaire'];
            foreach ($owner as $ow) {
                $name = $ow['name'] . " " . $ow['firstname'];
                $dispo = $ow['disponible'];
                if ($dispo == 1) {
                    $dispo = "disponible";
                } else if ($dispo == 2) {
                    $dispo = "épuisé";
                } else if ($dispo == 3) {
                    $dispo = "récupéré par équipe";
                }
                $date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
                $txt = $date_recept;

                $data .= $txt . " , ";
            }
            $data .= ";";

            $owner = $anticorps ['proprietaire'];
            foreach ($owner as $ow) {
                $name = $ow['name'] . " " . $ow['firstname'];
                $dispo = $ow['disponible'];
                if ($dispo == 1) {
                    $dispo = "disponible";
                } else if ($dispo == 2) {
                    $dispo = "épuisé";
                } else if ($dispo == 3) {
                    $dispo = "récupéré par équipe";
                }
                $date_recept = CoreTranslator::dateFromEn($ow['date_recept'], $lang);
                $txt = $ow['no_dossier'];

                $data .= $txt . " , ";
            }
            $data .= ";";
            $data .= "\r\n";
        }

        header("Content-Type: application/csv-tab-delimited-table");
        header("Content-disposition: filename=anticorps.csv");
        echo $data;
        return;
    }

    public function editAction($id_space, $id) {

        // Lists for the form	
        // get isotypes list
        $modelIsotype = new Isotype();
        $isotypesList = $modelIsotype->getIsotypes();

        // get sources list
        $modelSource = new Source();
        $sourcesList = $modelSource->getSources();

        // get applications list
        $modelApp = new AcApplication();
        $applicationsList = $modelApp->getApplications();

        // get applications list
        $modelStaining = new AcStaining();
        $stainingsList = $modelStaining->getStainings();

        // get especes list
        $especesModel = new Espece();
        $especes = $especesModel->getEspeces("nom");

        // get especes list
        $organesModel = new Organe();
        $organes = $organesModel->getOrganes("nom");

        // get proto list
        $protoModel = new AcProtocol();
        $protocols = $protoModel->getProtocolsNo();

        // get users List
        $modelUser = new CoreUser();
        $users = $modelUser->getUsersSummary('name');


        // get prelevements list
        $modelPrelevement = new Prelevement();
        $prelevements = $modelPrelevement->getPrelevements("nom");

        $modelstatus = new Status();
        $status = $modelstatus->getStatus();

        // get edit id
        $editID = $id;

        $anticorps = array();
        $modelAnticorps = new Anticorps();
        if ($editID != 0) {
            $anticorps = $modelAnticorps->getAnticorpsFromId($editID);
        } else {
            $anticorps = $modelAnticorps->getDefaultAnticorps();
        }

        $this->render(array(
            'id_space' => $id_space,
            'lang' => $this->getLanguage(),
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
        ));
    }

    public function editqueryAction($id_space) {

        $lang = $this->getLanguage();

        // add in anticorps table
        $id = $this->request->getParameterNoException("id");
        $nom = $this->request->getParameter("nom");
        $no_h2p2 = $this->request->getParameter("no_h2p2");
        $reference = $this->request->getParameter("reference");
        $clone = $this->request->getParameter("clone");
        $fournisseur = $this->request->getParameter("fournisseur");
        $lot = $this->request->getParameter("lot");
        $id_isotype = $this->request->getParameter("id_isotype");
        $id_source = $this->request->getParameter("id_source");
        $stockage = $this->request->getParameter("stockage");

        $id_proprietaire = $this->request->getParameter("id_proprietaire");
        $disponible = $this->request->getParameter("disponible");
        $date_recept = $this->request->getParameter("date_recept");
        $no_dossier = $this->request->getParameter("no_dossier");

        $espece = $this->request->getParameter("espece");
        $organe = $this->request->getParameter("organe");
        $valide = $this->request->getParameter("status");
        $ref_bloc = $this->request->getParameter("ref_bloc");
        $prelevement = $this->request->getParameter("prelevement");
        $dilution = $this->request->getParameter("dilution");
        $temps_incubation = $this->request->getParameterNoException("temps_incubation");
        $ref_protocol = $this->request->getParameter("ref_protocol");
        $comment = $this->request->getParameter("comment");

        $export_catalog = $this->request->getParameterNoException("export_catalog");
        $id_application = $this->request->getParameter("id_application");
        $id_staining = $this->request->getParameter("id_staining");
        $image_desc = $this->request->getParameter("image_desc");


        //print_r($export_catalog);
        //print_r($image_desc);

        $modelAnticorps = new Anticorps();
        $modelTissus = new Tissus();
        if ($id == "") {
            // add anticorps to table 
            $id = $modelAnticorps->addAnticorps($id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reference, $clone, $lot, $id_isotype, $stockage);
        
        } else {

            // update antibody
            $modelAnticorps->updateAnticorps($id, $id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reference, $clone, $lot, $id_isotype, $stockage);

            // remove all the owners
            $modelAnticorps->removeOwners($id);

            // remove all the Tissus
            $modelTissus->removeTissus($id);
        }

        // add the owner
        $i = -1;
        foreach ($id_proprietaire as $proprio) {
            $i++;
            //echo "date proprio = " . $date_recept[$i];
            if ($proprio > 1) {
                $date_r = CoreTranslator::dateToEn($date_recept[$i], $lang);
                $modelAnticorps->addOwner($proprio, $id, $date_r, $disponible[$i], $no_dossier[$i]);
            }
        }
        // add to the tissus table
        for ($i = 0; $i < count($espece); $i++) {
            $temps_incubation = "";
            $modelTissus->addTissus($id, $espece[$i], $organe[$i], $valide[$i], $ref_bloc[$i], $dilution[$i], $temps_incubation, $ref_protocol[$i], $prelevement[$i], $comment[$i]);
        
            // load tissus image
            $this->uploadTissusImage($id);
        }
            
        // add catalog informations
        if ($export_catalog == "on") {
            $export_catalog = 1;
        } else {
            $export_catalog = 0;
        }
        $modelAnticorps->setExportCatalog($id, $export_catalog);
        $modelAnticorps->setApplicationStaining($id, $id_staining, $id_application);
        $modelAnticorps->setImageDesc($id, $image_desc);
        if ($_FILES["image_url"]["name"] != "") {
            // download file
            $this->downloadIllustration();

            // set filename to database
            $modelAnticorps->setImageUrl($id, $_FILES["image_url"]["name"]);
        }

        // generate view
        $this->redirect("anticorps/" . $id_space . "/id");
    }

    public function uploadTissusImage($id) {
        //print_r($_FILES);
        
        $target_dir = "data/antibodies/";
        $filesCount = count($_FILES["tissusfiles"]);
        $modelTissus = new Tissus();
        for($i = 0 ; $i < count($filesCount) ; $i++){
            if ($_FILES["tissusfiles"]["name"][$i] != "") {
                $ext = pathinfo($_FILES["tissusfiles"]["name"][$i], PATHINFO_EXTENSION);
                
                $target_file = $target_dir . $_FILES["tissusfiles"]["name"][$i];
                $uploadOk = 1;
                // Check file size
                if ($_FILES["tissusfiles"]["size"][$i] > 500000000) {
                    return "Error: your file is too large.";
                    //$uploadOk = 0;
                }

                // Check if $uploadOk is set to 0 by an error
                if ($uploadOk == 0) {
                    return "Error: your file was not uploaded.";
                    // if everything is ok, try to upload file
                } else {
                    if (move_uploaded_file($_FILES["tissusfiles"]["tmp_name"][$i], $target_file)) {
                        return "The file logo file" . basename($_FILES["tissusfiles"]["name"][$i]) . " has been uploaded.";
                    } else {
                        return "Error, there was an error uploading your file.";
                    }
                }
                
                $modelTissus->setImageUrl($id, $target_dir . $id . "." . $ext);
            }
        }
    }
    
    protected function downloadIllustration() {
        $target_dir = "data/antibodies/";
        $target_file = $target_dir . $_FILES["image_url"]["name"];
        //echo "target file = " . $target_file . "<br/>";
        $uploadOk = 1;

        // Check file size
        if ($_FILES["image_url"]["size"] > 500000000) {
            return "Error: your file is too large.";
            //$uploadOk = 0;
        }

        // Check if $uploadOk is set to 0 by an error
        if ($uploadOk == 0) {
            return "Error: your file was not uploaded.";
            // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["image_url"]["tmp_name"], $target_file)) {
                return "The file logo file" . basename($_FILES["image_url"]["name"]) . " has been uploaded.";
            } else {
                return "Error, there was an error uploading your file.";
            }
        }
    }

    public function searchqueryAction($id_space) {

        $lang = $this->getLanguage();

        $searchColumn = $this->request->getParameter("searchColumn");
        $searchTxt = $this->request->getParameter("searchTxt");

        $anticorpsArray = "";
        $anticorpsModel = new Anticorps();
        if ($searchColumn == "0") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfo();
        } else if ($searchColumn == "Nom") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch("nom", $searchTxt);
        } else if ($searchColumn == "No_h2p2") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch("no_h2p2", $searchTxt);
        } else if ($searchColumn == "Fournisseur") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch("fournisseur", $searchTxt);
        } else if ($searchColumn == "Source") {

            $modelSource = new Source();
            $st = $modelSource->getIdFromName($searchTxt);
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch("id_source", $st);
        } else if ($searchColumn == "Reference") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch("reference", $searchTxt);
        } else if ($searchColumn == "Clone") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch("clone", $searchTxt);
        } else if ($searchColumn == "lot") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch("lot", $searchTxt);
        } else if ($searchColumn == "Isotype") {

            $modelIsotype = new Isotype();
            $st = $modelIsotype->getIdFromName($searchTxt);
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch("id_isotype", $st);
        } else if ($searchColumn == "Stockage") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch("stockage", $searchTxt);
        } else if ($searchColumn == "dilution") {
            $anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch("dilution", $searchTxt);
        } else if ($searchColumn == "temps_incub") {
            $anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch("temps_incubation", $searchTxt);
        } else if ($searchColumn == "ref_proto") {
            $anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch("ref_protocol", $searchTxt);
        } else if ($searchColumn == "espece") {
            $modelEspece = new Espece();
            $id = $modelEspece->getIdFromName($searchTxt);
            $anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch("espece", $searchTxt);
        } else if ($searchColumn == "organe") {
            //$modelOrgane = new Organe();
            //$id = $modelOrgane->getIdFromName($searchTxt);
            $anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch("organe", $searchTxt);
        } else if ($searchColumn == "valide") {
            $anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch("valide", $searchTxt);
        } else if ($searchColumn == "ref_bloc") {
            $anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch("ref_bloc", $searchTxt);
        } else if ($searchColumn == "nom_proprio") {
            $anticorpsArray = $anticorpsModel->getAnticorpsProprioSearch("nom_proprio", $searchTxt);
        } else if ($searchColumn == "disponibilite") {
            $anticorpsArray = $anticorpsModel->getAnticorpsProprioSearch("disponibilite", $searchTxt);
        } else if ($searchColumn == "date_recept") {
            $anticorpsArray = $anticorpsModel->getAnticorpsProprioSearch("date_recept", CoreTranslator::dateToEn($searchTxt, $lang));
        }

        $modelstatus = new Status();
        $status = $modelstatus->getStatus();

        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space, 'anticorpsArray' => $anticorpsArray,
            'searchColumn' => $searchColumn, 'searchTxt' => $searchTxt,
            'status' => $status
                ), "index");
    }

    public function advsearchqueryAction($id_space, $source = "") {


        if ($source == "index") {

            //print_r($_SESSION["ac_advSearch"]);
            //return;
            $searchName = $_SESSION["ac_advSearch"]["searchName"];
            $searchNoH2P2 = $_SESSION["ac_advSearch"]["searchNoH2P2"];
            $searchSource = $_SESSION["ac_advSearch"]["searchSource"];
            $searchCible = $_SESSION["ac_advSearch"]["searchCible"];
            $searchValide = $_SESSION["ac_advSearch"]["searchValide"];
            $searchResp = $_SESSION["ac_advSearch"]["searchResp"];
        } else {
            $searchName = $this->request->getParameterNoException("searchName");
            $searchNoH2P2 = $this->request->getParameterNoException("searchNoH2P2");
            $searchSource = $this->request->getParameterNoException("searchSource");
            $searchCible = $this->request->getParameterNoException("searchCible");
            $searchValide = $this->request->getParameterNoException("searchValide");
            $searchResp = $this->request->getParameterNoException("searchResp");
        }

        $_SESSION["ac_advSearch"] = array("searchName" => $searchName,
            "searchNoH2P2" => $searchNoH2P2,
            "searchSource" => $searchSource,
            "searchCible" => $searchCible,
            "searchValide" => $searchValide,
            "searchResp" => $searchResp);

        $anticorpsModel = new Anticorps();
        $anticorpsArray = $anticorpsModel->searchAdv($searchName, $searchNoH2P2, $searchSource, $searchCible, $searchValide, $searchResp);
        //$anticorpsArray = $anticorpsModel->getAnticorpsInfo("id");


        $modelstatus = new Status();
        $status = $modelstatus->getStatus();
        
        $lang = $this->getLanguage();

        $this->render(array(
            'id_space' => $id_space,
            'anticorpsArray' => $anticorpsArray,
            'searchName' => $searchName,
            'searchNoH2P2' => $searchNoH2P2,
            'searchSource' => $searchSource,
            'searchCible' => $searchCible,
            'searchValide' => $searchValide,
            'searchResp' => $searchResp,
            'status' => $status,
            'lang' => $lang
                ), "indexAction");
    }

    public function deleteAction($id_space, $id) {

        // get source info
        $anticorpsModel = new Anticorps();
        $anticorpsModel->delete($id);

        $this->redirect("anticorps/" . $id_space . "/id");
    }

}

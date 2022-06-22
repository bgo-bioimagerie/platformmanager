<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/AntibodiesTranslator.php';
require_once 'Modules/antibodies/Model/Anticorps.php';
require_once 'Modules/antibodies/Model/Espece.php';
require_once 'Modules/antibodies/Model/Status.php';
require_once 'Modules/antibodies/Model/Organe.php';
require_once 'Modules/antibodies/Model/Prelevement.php';
require_once 'Modules/antibodies/Model/AcProtocol.php';
require_once 'Modules/antibodies/Model/AcOwner.php';

require_once 'Modules/antibodies/Form/TissusForm.php';
require_once 'Modules/antibodies/Form/OwnerForm.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class AntibodieslistController extends AntibodiesController {

    private $antibody;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        $this->antibody = new Anticorps();
        $this->noSideMenu = true;

        //$this->checkAuthorizationMenu("antibodies");
        
    }

    protected function isAdvSearch(){
        
        if (isset($_SESSION["ac_advSearch"])){
            $s = $_SESSION["ac_advSearch"];
            if( $s['searchName'] == "" &&  $s['searchNoH2P2'] == "" 
                    && $s['searchSource'] == "" && $s['searchCible'] == "" 
                    && $s['searchValide'] == 0 && $s['searchResp'] == ""){
                return false;
            }
            return true;
        }
        return false;
                
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $sortentry = "") {

        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        
        if ($this->isAdvSearch()) {
            $this->advsearchqueryAction($id_space, "index");
            return;
        }

        // get the antibodies list
        if($sortentry == ""){
            $sortentry = "A";
        }

        $anticorpsModel = new Anticorps();
        $anticorpsArray = $anticorpsModel->getAnticorpsInfo($id_space, $sortentry);
        $modelstatus = new Status();
        $status = $modelstatus->getStatus($id_space);

        return $this->render(array(
            'id_space' => $id_space, 'anticorpsArray' => $anticorpsArray,
            'status' => $status, 'lang' => $this->getLanguage(), 'letter' => $sortentry
        ));
    }

    public function anticorpscsvAction($id_space) {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        // database query
        $anticorpsModel = new Anticorps();
        $anticorpsArray = $anticorpsModel->getAnticorpsInfo($id_space, "");

        $modelstatus = new Status();
        $status = $modelstatus->getStatus($id_space);

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
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        // informations form
        if ($id != 0) {
            $anticorps = $this->antibody->getAnticorpsFromId($id_space, $id);
        } else {
            $anticorps = $this->antibody->getDefaultAnticorps();
        }
        $form = $this->createEditForm($id_space, $anticorps, $id);
        if ($form->check()) {

            $idNew = $this->antibody->setAntibody($id, $id_space, $form->getParameter("name"), $form->getParameter("no_h2p2"), $form->getParameter("fournisseur"), $form->getParameter("id_source"), $form->getParameter("reference"), $form->getParameter("clone"), $form->getParameter("lot"), $form->getParameter("id_isotype"), $form->getParameter("stockage")
            );
            
            $this->antibody->setApplicationStaining($id_space, $idNew, $form->getParameter("id_staining"), $form->getParameter("id_application")
            );

            $_SESSION["flash"] = AntibodiesTranslator::AntibodyInfoHaveBeenSaved($lang);
            $_SESSION["flashClass"] = 'success';

            $this->redirect('anticorpsedit/' . $id_space . '/' . $idNew);
            return;
        }

        // Tissus table
        $modelTissus = new Tissus();
        $tissus = $modelTissus->getInfoForAntibody($id_space, $id);
        $tissusTable = $this->createTissusTable($id_space, $tissus);
        
        // Add Catalogue form
        $catalogFormHtml = "";
        if ($id > 0) {
            $catalogForm = new Form($this->request, "setToCatalogForm");
            $catalogForm->addSelect("export_catalog", AntibodiesTranslator::Export_catalog($lang), array(CoreTranslator::no($lang), 
                CoreTranslator::yes($lang)), array(0, 1), $anticorps["export_catalog"]);
            $catalogForm->setValidationButton(CoreTranslator::Save($lang), 'anticorpsedit/' . $id_space . '/' . $id);
            $catalogForm->setColumnsWidth(2, 10);
            
            if ($catalogForm->check()) {
                $this->antibody->setExportCatalog($id_space, $id, $form->getParameter("export_catalog"));
            
                $_SESSION["flash"] = AntibodiesTranslator::AntibodyInfoHaveBeenSaved($lang);
                $_SESSION["flashClass"] = 'success';

                $this->redirect('anticorpsedit/' . $id_space . '/' . $id);
                return;
            }
            $catalogFormHtml = $catalogForm->getHtml($lang);
        }

        // Owner Table
        $modelOwner = new AcOwner();
        $owners = $modelOwner->getInfoForAntibody($id_space, $id);
        
        $ownersTable = $this->createOwnerTable($id_space, $owners);

        $tissusFormGenerator = new TissusForm($this->request, "tissusForm", "antibodiesedittissus/" . $id_space);
        $tissusFormGenerator->setSpace($id_space);
        $tissusFormGenerator->setLang($lang);
        $tissusFormGenerator->render();
        
        $ownerFormGenerator = new OwnerForm($this->request, "ownerForm", "antibodieseditowner/" . $id_space);
        $ownerFormGenerator->setSpace($id_space);
        $ownerFormGenerator->setLang($lang);
        $ownerFormGenerator->render();

        return $this->render(array(
            "id_space" => $id_space, "id" => $id,
            "lang" => $this->getLanguage(),
            "form" => $form->getHtml($lang),
            "tissus" => $tissus,
            "owners" => $owners,
            "tissusTable" => $tissusTable,
            "ownersTable" => $ownersTable,
            "formtissus" => $tissusFormGenerator->getHtml(),
            "formowner" => $ownerFormGenerator->getHtml(),
            "formCatalog" => $catalogFormHtml
        ));
    }

    public function edittissusAction($id_space) {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        $id = $this->request->getParameter("id");
        $id_antibody = $this->request->getParameter("id_antibody");
        $ref_protocol = $this->request->getParameter("ref_protocol");
        $dilution = $this->request->getParameter("dilution");
        $comment = $this->request->getParameter("comment");
        $espece = $this->request->getParameter("espece");
        $organe = $this->request->getParameter("organe");
        $status = $this->request->getParameter("status");
        $ref_bloc = $this->request->getParameter("ref_bloc");
        $prelevement = $this->request->getParameter("prelevement");
        $temps_incubation = $this->request->getParameterNoException("temps_incubation");

        $modelTissus = new Tissus();
        $idNew = $modelTissus->setTissus($id_space, $id, $id_antibody, $espece, $organe, $status, $ref_bloc, $dilution, $temps_incubation, $ref_protocol, $prelevement, $comment);

        $this->uploadTissusImage($id_space ,$idNew);

        $this->redirect("anticorpsedit/" . $id_space . "/" . $id_antibody);
    }

    public function editownerAction($id_space) {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        $id = $this->request->getParameter("owner_id");
        $id_antibody = $this->request->getParameter("owner_id_anticorps");

        $id_utilisateur = $this->request->getParameter("owner_id_user");
        $disponible = $this->request->getParameter("owner_disponible");
        $no_dossier = $this->request->getParameter("owner_no_dossier");

        $lang = $this->getLanguage();
        $date_recept = CoreTranslator::dateToEn($this->request->getParameter("owner_date_recept"), $lang);

        $model = new AcOwner();
        $model->setOwner($id_space, $id, $id_antibody, $id_utilisateur, $disponible, $date_recept, $no_dossier);

        $this->redirect("anticorpsedit/" . $id_space . "/" . $id_antibody);
    }

    protected function createTissusTable($id_space, $data) {
        
        $lang = $this->getLanguage();

        $table = new TableView("tissusTable");
        $table->setTitle(AntibodiesTranslator::Tissus($lang));
        $headers = array(
            "ref_protocol" => AntibodiesTranslator::Ref_protocol($lang),
            "dilution" => AntibodiesTranslator::Dilution($lang),
            "comment" => AntibodiesTranslator::Comment($lang),
            "espece" => AntibodiesTranslator::Espece($lang),
            "organe" => AntibodiesTranslator::Organe($lang),
            "status" => AntibodiesTranslator::Valide($lang),
            "ref_bloc" => AntibodiesTranslator::Ref_bloc($lang),
            "prelevement" => AntibodiesTranslator::Prelevement($lang),
            "image_url" => array("title" => AntibodiesTranslator::Image($lang), "type" => "image", "base_url" => "data/antibodies/"),
        );

        $table->addLineEditButton("edittissus", "id", true);
        $table->addDeleteButton("deletetissus/".$id_space, "id", "ref_protocol");
        $tableHtml = $table->view($data, $headers);
        return $tableHtml;
    }

    public function deletetissusAction($id_space, $id_tissus){
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        $modelTissus = new Tissus();
        $tissus = $modelTissus->getTissusById($id_space ,$id_tissus);
        
        
        $modelTissus->delete($id_space ,$id_tissus);
        
        $this->redirect('anticorpsedit/'.$id_space.'/'.$tissus['id_anticorps']);
    }
    
    protected function createOwnerTable($id_space, $data) {

        $lang = $this->getLanguage();

        $table = new TableView("ownerTable");
        $table->setTitle(AntibodiesTranslator::Owner($lang));
        $headers = array(
            "utilisateur" => CoreTranslator::User($lang),
            "disponible" => AntibodiesTranslator::Disponible($lang),
            "date_recept" => AntibodiesTranslator::Date_recept($lang),
            "no_dossier" => AntibodiesTranslator::No_dossier($lang),
        );

        $modelUser = new CoreUser();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["utilisateur"] = $modelUser->getUserFUllName($data[$i]["id_utilisateur"]);
            $data[$i]["date_recept"] = CoreTranslator::dateFromEn($data[$i]["date_recept"], $lang);
            if ($data[$i]["disponible"] == 1) {
                $data[$i]["disponible"] = "disponible";
            } else if ($data[$i]["disponible"] == 2) {
                $data[$i]["disponible"] = "épuisé";
            } else if ($data[$i]["disponible"] == 3) {
                $data[$i]["disponible"] = "récupéré par équipe";
            }
        }

        $table->addLineEditButton("editowner", "id", true);
        $table->addDeleteButton('deleteowner/'.$id_space, 'id', 'utilisateur');
        $tableHtml = $table->view($data, $headers);
        return $tableHtml;
    }
    
    public function deleteownerAction($id_space, $id_owner){
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        $modelOwner = new AcOwner();
        $owner = $modelOwner->get($id_space ,$id_owner);
        $modelOwner->delete($id_space ,$id_owner);
        
        $this->redirect('anticorpsedit/'.$id_space.'/'.$owner['id_anticorps']);
    }

    protected function createEditForm($id_space, $anticorps, $id) {

        $lang = $this->getLanguage();
        $form = new Form($this->request, 'antibodyEditForm');
        $form->setTitle(AntibodiesTranslator::AntibodyInfo($lang));

        $form->addText("name", CoreTranslator::Name($lang), true, $anticorps["nom"]);
        $form->addText("no_h2p2", AntibodiesTranslator::Number($lang), false, $anticorps["no_h2p2"]);
        $form->addText("fournisseur", AntibodiesTranslator::Provider($lang), false, $anticorps["fournisseur"]);

        $modelSource = new Source();
        $sourcesList = $modelSource->getForList($id_space);
        $form->addSelect("id_source", AntibodiesTranslator::Source($lang), $sourcesList["names"], $sourcesList["ids"], $anticorps["id_source"]);

        $form->addText("reference", AntibodiesTranslator::Reference($lang), false, $anticorps["reference"]);
        $form->addText("clone", AntibodiesTranslator::AcClone($lang), false, $anticorps["clone"]);
        $form->addText("lot", AntibodiesTranslator::Lot($lang), false, $anticorps["lot"]);

        $modelIsotype = new Isotype();
        $isotypesList = $modelIsotype->getForList($id_space);
        $form->addSelect("id_isotype", AntibodiesTranslator::Isotype($lang), $isotypesList["names"], $isotypesList["ids"], $anticorps["id_isotype"]);

        $form->addText("stockage", AntibodiesTranslator::Stockage($lang), false, $anticorps["stockage"]);

        $modelApp = new AcApplication();
        $applicationsList = $modelApp->getForList($id_space);
        $form->addSelect("id_application", AntibodiesTranslator::Application($lang), $applicationsList["names"], $applicationsList["ids"], $anticorps["id_application"]);

        $modelStaining = new AcStaining();
        $stainingsList = $modelStaining->getForList($id_space);
        $form->addSelect("id_staining", AntibodiesTranslator::Staining($lang), $stainingsList["names"], $stainingsList["ids"], $anticorps["id_staining"]);

        //$form->addSelect("export_catalog", AntibodiesTranslator::Export_catalog($lang), array(CoreTranslator::no($lang), CoreTranslator::yes($lang)), array(0, 1), $anticorps["export_catalog"]);

        $form->setValidationButton(CoreTranslator::Save($lang), 'anticorpsedit/' . $id_space . "/" . $id);
        $form->setColumnsWidth(2, 8);
        return $form;
    }

    public function uploadTissusImage($id_space ,$id) {
        //print_r($_FILES);

        $target_dir = "data/antibodies/";
        $modelTissus = new Tissus();
        if ($_FILES["image_url"]["name"] != "") {
            //echo "upload image " . $_FILES["tissusfiles"]["name"][$i] . "<br/>";
            //$ext = pathinfo($_FILES["image_url"]["name"], PATHINFO_EXTENSION);
            $fileName = $id_space."_".$_FILES["image_url"]["name"];
            $fileNameOK = preg_match("/^[0-9a-zA-Z\-_\.]+$/", $fileName, $matches);
            if(! $fileNameOK) {
                throw new PfmFileException("invalid file name, must be alphanumeric:  [0-9a-zA-Z\-_\.]+", 403);
            }

            $target_file = $target_dir . $fileName;
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
                    //echo "set image URL to antibody " . $id . 
                    $modelTissus->setImageUrl($id_space ,$id, $fileName);
                    return "The image file" . basename($_FILES["image_url"]["name"]) . " has been uploaded.";
                } else {
                    return "Error, there was an error uploading your file.";
                }
            }
        }
    }

    public function searchqueryAction($id_space) {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $searchColumn = $this->request->getParameter("searchColumn");
        $searchTxt = $this->request->getParameter("searchTxt");

        $anticorpsArray = "";
        $anticorpsModel = new Anticorps();
        if ($searchColumn == "0") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfo($id_space);
        } else if ($searchColumn == "Nom") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space ,"nom", $searchTxt);
        } else if ($searchColumn == "No_h2p2") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space ,"no_h2p2", $searchTxt);
        } else if ($searchColumn == "Fournisseur") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space ,"fournisseur", $searchTxt);
        } else if ($searchColumn == "Source") {

            $modelSource = new Source();
            $st = $modelSource->getIdFromName($searchTxt, $id_space);
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "id_source", $st);
        } else if ($searchColumn == "Reference") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "reference", $searchTxt);
        } else if ($searchColumn == "Clone") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "clone", $searchTxt);
        } else if ($searchColumn == "lot") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "lot", $searchTxt);
        } else if ($searchColumn == "Isotype") {

            $modelIsotype = new Isotype();
            $st = $modelIsotype->getIdFromName($searchTxt, $id_space);
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "id_isotype", $st);
        } else if ($searchColumn == "Stockage") {
            $anticorpsArray = $anticorpsModel->getAnticorpsInfoSearch($id_space, "stockage", $searchTxt);
        } else if ($searchColumn == "dilution") {
            $anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch($id_space, "dilution", $searchTxt);
        } else if ($searchColumn == "temps_incub") {
            $anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch($id_space, "temps_incubation", $searchTxt);
        } else if ($searchColumn == "ref_proto") {
            $anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch($id_space, "ref_protocol", $searchTxt);
        } else if ($searchColumn == "espece") {
            $modelEspece = new Espece();
            $id = $modelEspece->getIdFromName($searchTxt, $id_space);
            $anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch($id_space, "espece", $searchTxt);
        } else if ($searchColumn == "organe") {
            //$modelOrgane = new Organe();
            //$id = $modelOrgane->getIdFromName($searchTxt);
            $anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch($id_space, "organe", $searchTxt);
        } else if ($searchColumn == "valide") {
            $anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch($id_space, "valide", $searchTxt);
        } else if ($searchColumn == "ref_bloc") {
            $anticorpsArray = $anticorpsModel->getAnticorpsTissusSearch($id_space, "ref_bloc", $searchTxt);
        } else if ($searchColumn == "nom_proprio") {
            $anticorpsArray = $anticorpsModel->getAnticorpsProprioSearch($id_space,"nom_proprio", $searchTxt);
        } else if ($searchColumn == "disponibilite") {
            $anticorpsArray = $anticorpsModel->getAnticorpsProprioSearch($id_space,"disponibilite", $searchTxt);
        } else if ($searchColumn == "date_recept") {
            $anticorpsArray = $anticorpsModel->getAnticorpsProprioSearch($id_space,"date_recept", CoreTranslator::dateToEn($searchTxt, $lang));
        }

        $modelstatus = new Status();
        $status = $modelstatus->getStatus($id_space);

        return $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space, 'anticorpsArray' => $anticorpsArray,
            'searchColumn' => $searchColumn, 'searchTxt' => $searchTxt,
            'status' => $status
                ), "index");
    }

    public function advsearchqueryAction($id_space, $source = "") {
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);

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
        $anticorpsArray = $anticorpsModel->searchAdv($id_space, $searchName, $searchNoH2P2, $searchSource, $searchCible, $searchValide, $searchResp);
        //$anticorpsArray = $anticorpsModel->getAnticorpsInfo("id");


        $modelstatus = new Status();
        $status = $modelstatus->getStatus($id_space);

        $lang = $this->getLanguage();

        return $this->render(array(
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
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        $form = new Form($this->request, "antibodiesdeleteform");
        $form->addComment(AntibodiesTranslator::ConfirmDeleteAntibody($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "antibodydeleteconfirmed/".$id_space.'/'.$id);
        
        return $this->render(array("id_space" => $id_space, "formHtml" => $form->getHtml($lang)));
        
    }
    
    public function deleteconfirmedAction($id_space, $id){
        $this->checkAuthorizationMenuSpace("antibodies", $id_space, $_SESSION["id_user"]);
        $anticorpsModel = new Anticorps();
        $anticorpsModel->delete($id_space ,$id);

        $this->redirect("anticorps/" . $id_space);
    }

}

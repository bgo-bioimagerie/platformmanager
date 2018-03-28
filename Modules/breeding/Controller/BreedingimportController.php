<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';

require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/breeding/Model/BrClient.php';
require_once 'Modules/breeding/Model/BrPricing.php';
require_once 'Modules/breeding/Model/BrCategory.php';
require_once 'Modules/breeding/Model/BrCompany.php';
require_once 'Modules/breeding/Model/BrDeliveryMethod.php';
require_once 'Modules/breeding/Model/BrProduct.php';
require_once 'Modules/breeding/Model/BrBatch.php';
require_once 'Modules/breeding/Model/BrChipping.php';
require_once 'Modules/breeding/Model/BrLosseType.php';
require_once 'Modules/breeding/Model/BrTreatment.php';
require_once 'Modules/breeding/Model/BrLosse.php';
require_once 'Modules/breeding/Model/BrSale.php';
require_once 'Modules/breeding/Model/BrSaleItem.php';
require_once 'Modules/breeding/Model/BrContactType.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class BreedingimportController extends CoresecureController {

    /**
     * User model object
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->model = new BrBatch ();
        $_SESSION["openedNav"] = "breeding";
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Import access database
     */
    public function indexAction($id_space) {

        $dsn_old = 'mysql:host=localhost;dbname=movedb;charset=utf8';
        $login_old = "root";
        $pwd_old = "root";

        $pdo_old = new PDO($dsn_old, $login_old, $pwd_old, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));


        echo 'import users <br/>';
        $this->importUsers($pdo_old, $id_space);
        echo 'import pricing <br/>';
        $this->importPricing($pdo_old, $id_space);
        echo 'import clients <br/>';
        $this->importClients($pdo_old, $id_space);
        echo 'import Categories <br/>';
        $this->importCategories($pdo_old, $id_space);
        echo 'import My company <br/>';
        $this->importMyCompany($pdo_old, $id_space);
        echo 'import Delivery Methods <br/>';
        $this->importDeliveryMethod($pdo_old, $id_space);
        echo 'import Products <br/>';
        $this->importProduct($pdo_old, $id_space);
        echo 'import Batch <br/>';
        $this->importBatch($pdo_old, $id_space);
        echo 'import Chipping <br/>';
        $this->importChipping($pdo_old);
        echo 'import Losse Type <br/>';
        $this->importLosseType($pdo_old, $id_space);
        echo 'import Treatment <br/>';
        $this->importTreatment($pdo_old);
        echo 'import Losse <br/>';
        $this->importLosse($pdo_old, $id_space);
        echo 'import Sale <br/>';
        $this->importSale($pdo_old, $id_space);
        echo 'recalculate batch count <br/>';
        $this->calculateBatchsCount($id_space);
        echo 'sexage <br/>';
        $this->sexage($id_space);
    }

    protected function importUsers($pdo_old, $id_space) {
        $sql = "SELECT * FROM Employés";
        $result = $pdo_old->query($sql);
        $data_old = $result->fetchAll();

        $model = new CoreUser();
        $modelSpace = new CoreSpace();

        foreach ($data_old as $do) {

            $name = $do["NomFamille"];
            $firstname = $do["Prénom"];
            $login = $do["NomFamille"];
            $pwd = "123456";
            $email = "";
            $phone = "";
            $unit = 1;
            $is_responsible = 0;
            $status_id = 1;
            $date_convention = date("Y-m-d", time());
            $date_end_contract = "";

            $id_user = $model->add($name, $firstname, $login, $pwd, $email, $phone, $unit, $is_responsible, $status_id, $date_convention, $date_end_contract);
            $modelSpace->setUser($id_user, $id_space, 3);
        }
    }

    protected function importPricing($pdo_old, $id_space) {

        $modelPricing = new BrPricing();
        $modelPricing->set(0, $id_space, "Public");
        $modelPricing->set(0, $id_space, "Privé");
    }

    protected function importClients($pdo_old, $id_space) {

        $sql = "SELECT * FROM Clients";
        $result = $pdo_old->query($sql);
        $clients_old = $result->fetchAll();

        $model = new BrClient();
        $modelPricing = new BrPricing();

        foreach ($clients_old as $cliento) {

            $name = $cliento["NomClient"];
            $contact_name = $cliento["NomContact"];
            $institution = $cliento["Etablissement"];
            $building_floor = $cliento["Bâtiment/Etage"];
            $service = $cliento["Service"];
            $address = $cliento["Adresse"];
            $zip_code = $cliento["CodePostal"];
            $city = $cliento["Ville"];
            $country = $cliento["Pays"];
            $phone = $cliento["NuméroTél"];
            $email = $cliento["Email"];
            $pricing = $modelPricing->getIdFromName($cliento["SecteurActivité"]);

            $model->set(0, $id_space, $name, $contact_name, $institution, $building_floor, $service, $address, $zip_code, $city, $country, $phone, $email, $pricing);
        }
    }

    protected function importCategories($pdo_old, $id_space) {

        $sql = "SELECT * FROM Catégories";
        $result = $pdo_old->query($sql);
        $data_old = $result->fetchAll();

        $model = new BrCategory();

        foreach ($data_old as $do) {

            $name = $do["NomCatégorie"];
            $model->set(0, $id_space, $name);
        }
    }

    protected function importMyCompany($pdo_old, $id_space) {

        $sql = "SELECT * FROM `Informations sur ma société`";
        $result = $pdo_old->query($sql);
        $data_old = $result->fetchAll();

        $model = new BrCompany();

        foreach ($data_old as $do) {

            $name = $do["NomSociété"];
            $address = $do["Adresse"];
            $zipcode = $do["CodePostal"];
            $city = $do["Ville"];
            $county = $do["DépartementOuRégion"];
            $country = $do["Pays"];
            $tel = $do["NuméroTél"];
            $fax = $do["NumTélécopie"];
            $email = "";
            $approval_number = $do["agrément établissement"];

            $model->set($id_space, $name, $address, $zipcode, $city, $county, $country, $tel, $fax, $email, $approval_number);
        }
    }

    protected function importDeliveryMethod($pdo_old, $id_space) {
        $sql = "SELECT * FROM `Modes expédition";
        $result = $pdo_old->query($sql);
        $data_old = $result->fetchAll();

        $model = new BrDeliveryMethod();

        foreach ($data_old as $do) {

            $name = $do["ModeExpédition"];
            $model->set(0, $id_space, $name);
        }
    }

    protected function importProduct($pdo_old, $id_space) {
        $sql = "SELECT * FROM Produits";
        $result = $pdo_old->query($sql);
        $data_old = $result->fetchAll();

        $model = new BrProduct();

        foreach ($data_old as $do) {
            $name = $do["NomProduit"];
            $code = $do["Codeproduit"];
            $description = $do["DescriptionProduit"];
            $id_category = $this->getNewCategoryId($pdo_old, $do["RéfCatégorie"]);
            $model->set(0, $id_space, $name, $code, $description, $id_category);
        }
    }

    protected function getNewCategoryId($pdo_old, $old_id) {

        $result = $pdo_old->prepare('SELECT NomCatégorie FROM Catégories WHERE RéfCatégorie=?');
        $result->execute(array($old_id));
        $data_old = $result->fetch();
        $name = $data_old[0];

        $model = new BrCategory();
        return $model->getIdFromName($name);
    }

    protected function importBatch($pdo_old, $id_space) {
        $sql = "SELECT * FROM lot";
        $result = $pdo_old->query($sql);
        $data_old = $result->fetchAll();

        $model = new BrBatch();

        foreach ($data_old as $do) {

            $dateArray = explode(" ", $do["date création"]);

            $destID = 1;
            if ($do["destination"] == "Labo") {
                $destID = 2;
            }

            $reference = $do["CodeLot"];
            $created = $dateArray[0];
            $id_male_spawner = $this->getNewBatchId($pdo_old, $do["géniteur_mâle"]);
            $id_female_spawner = $this->getNewBatchId($pdo_old, $do["géniteur_femelle"]);
            $id_destination = $destID;
            $id_product = $this->getNewProductId($pdo_old, $do["RéfProduit"]);
            $chipped = $do["pucelé"];
            $comment = $do["commentaires"];

            $id = $model->set(0, $id_space, $reference, $created, $id_male_spawner, $id_female_spawner, $id_destination, $id_product, $chipped, $comment);
            $model->setQuantityStart($id, $do["quantité"]);
        }
    }

    protected function getNewProductId($pdo_old, $id_old) {
        $result = $pdo_old->prepare('SELECT Codeproduit FROM Produits WHERE RéfProduit=?');
        $result->execute(array($id_old));
        $data_old = $result->fetch();
        $name = $data_old[0];

        $model = new BrProduct();
        return $model->getIdFromName($name);
    }

    protected function getNewBatchId($pdo_old, $id_old) {

        $result = $pdo_old->prepare('SELECT CodeLot FROM lot WHERE RéfLot=?');
        $result->execute(array($id_old));
        $data_old = $result->fetch();
        $name = $data_old[0];

        $model = new BrBatch();
        return $model->getIdFromName($name);
    }

    protected function importChipping($pdo_old) {
        $sql = "SELECT * FROM Puces";
        $result = $pdo_old->query($sql);
        $data_old = $result->fetchAll();

        $model = new BrChipping();

        foreach ($data_old as $do) {

            $id_batch = $this->getNewBatchId($pdo_old, $do["RéfLot"]);
            $date = $do["date"];
            $chip_number = $do["Numéro_puce"];
            $comment = $do["Commentaires"];

            $model->set(0, $id_batch, $date, $chip_number, $comment);
        }
    }

    public function importLosseType($pdo_old, $id_space) {

        $sql = "SELECT * FROM `Service Perte`";
        $result = $pdo_old->query($sql);
        $data_old = $result->fetchAll();

        $model = new BrLosseType();

        foreach ($data_old as $do) {

            $name = $do["TypePerte"];
            $model->set(0, $id_space, $name);
        }
    }

    protected function importTreatment($pdo_old) {
        $sql = "SELECT * FROM traitement";
        $result = $pdo_old->query($sql);
        $data_old = $result->fetchAll();

        $model = new BrTreatment();

        foreach ($data_old as $do) {

            $id_batch = $this->getNewBatchId($pdo_old, $do["RéfLot"]);
            $date = $this->formatDate($do["date_operation"]);
            $antibiotic = $do["antibiotique"];
            $suppressor = $do["anti-parasite"];
            $water = $do["eau"];
            $food = $do["nourriture"];
            $comment = $do["commentaires"];
            $model->set(0, $id_batch, $date, $antibiotic, $suppressor, $water, $food, $comment);
        }
    }

    protected function importLosse($pdo_old, $id_space) {
        $sql = "SELECT * FROM `Transactions inventaire` WHERE TypePerte IS NOT NULL";
        $result = $pdo_old->query($sql);
        $data_old = $result->fetchAll();

        $model = new BrLosse();
        $modelLosseType = new BrLosseType();

        foreach ($data_old as $do) {

            if ($do["UnitésPertes"] > 0){
                $id_batch = $this->getNewBatchId($pdo_old, $do["RéfLot"]);
                $date = $do["DateTransaction"];
                $id_user = 0;
                $quantity = $do["UnitésPertes"];
                $comment = $do["DescriptionTransaction"];
                $id_type = $modelLosseType->getIdFromName($do["TypePerte"]);

                $model->set(0, $id_space, $id_batch, $date, $id_user, $quantity, $comment, $id_type);
            }
        }
    }

    protected function importSale($pdo_old, $id_space) {
        $sql = "SELECT * FROM `Bons de livraison`";
        $result = $pdo_old->query($sql);
        $data_old = $result->fetchAll();

        $model = new BrSale();
        $modelItem = new BrSaleItem();

        foreach ($data_old as $do) {

            $id_enterd_by = $this->getNewUserId($pdo_old, $do["RéfEmployé"]);
            $id_client = $this->getNewClientId($pdo_old, $do["RéfClient"]);
            $id_delivery_method = $this->getNewDeliveryMethodId($pdo_old, $do["RéfModeExpédition"]);
            $id_status = $this->getSaleStatusFromName($do["EtatVente"]);
            $delivery_expected = $this->formatDate($do["DatePromesse"]);
            $purchase_order_num = $do["NuméroBonCommande"];
            $id_contact_type = $this->getTypeContactFromName($id_space, $do["typeContact"]);
            $further_information = $do["DescriptionBonLivraison"];

            $id_sale = $model->set(0, $id_space, $id_enterd_by, $id_client, $id_delivery_method, $id_status, $delivery_expected, $purchase_order_num, $id_contact_type, $further_information);

            $model->setDeliveryDate($id_sale, $this->formatDate($do["DateExpédition"]));
            $model->setDeliveryPrice($id_sale, $do["FraisTransport"]);
            $model->setPackingPrice($id_sale, $do["Emballage"]);
            $model->cancel($id_sale, $do["CauseAnnulation"], "0000-00-00");

            // import itemps
            $result = $pdo_old->prepare("SELECT * FROM `Transactions inventaire` WHERE RéfBonLivraison=?");
            $result->execute(array($do["RéfBonLivraison"]));
            $item_old = $result->fetchAll();

            foreach ($item_old as $io) {

                if ($io["UnitésVendues"] > 0) {

                    $date = $this->formatDate($io["DateTransaction"]);
                    $id_batch = $this->getNewBatchId($pdo_old, $io["RéfLot"]);
                    $requested_product = $io["ProduitDemandé"];
                    $requested_quantity = $io["QuantitéDemandée"];
                    $quantity = $io["UnitésVendues"];
                    $comment = $io["DescriptionTransaction"];

                    $modelItem->set(0, $id_sale, $date, $id_batch, $requested_product, $requested_quantity, $quantity, $comment);
                }
            }
        }
    }

    protected function getNewUserId($pdo_old, $id_old) {
        $result = $pdo_old->prepare("SELECT NomFamille FROM Employés WHERE RéfEmployé=?");
        $result->execute(array($id_old));
        $data_old = $result->fetch();

        $name_old = $data_old[0];

        $modelUser = new CoreUser();
        return $modelUser->getIdFromLogin($name_old);
    }

    protected function getNewClientId($pdo_old, $id_old) {
        $result = $pdo_old->prepare("SELECT NomClient FROM Clients WHERE RéfClient=?");
        $result->execute(array($id_old));
        $data_old = $result->fetch();

        $name_old = $data_old[0];

        $model = new BrClient();
        return $model->getIdFromName($name_old);
    }

    protected function getNewDeliveryMethodId($pdo_old, $id_old) {
        $result = $pdo_old->prepare("SELECT RéfModeExpédition FROM `Modes expédition` WHERE ModeExpédition=?");
        $result->execute(array($id_old));
        $data_old = $result->fetch();

        $name_old = $data_old[0];

        $model = new BrDeliveryMethod();
        $model->getIdFromName($name_old);
    }

    protected function getSaleStatusFromName($name) {
        if ($name == "Saisie") {
            return 1;
        }
        if ($name == "EnCours") {
            return 2;
        }
        if ($name == "Vendu") {
            return 3;
        }
        if ($name == "Annulé") {
            return 4;
        }
        if ($name == "Perdu") {
            return 5;
        }
    }

    protected function formatDate($date) {
        $dateArray = explode(" ", $date);
        if (count($dateArray) > 1) {
            return $dateArray[0];
        }
        return "0000-00-00";
    }

    protected function getTypeContactFromName($id_space, $contactName) {

        $model = new BrContactType();
        $id = $model->exists($contactName);
        if ($id > 0) {
            return $id;
        } else {
            return $model->set(0, $id_space, $contactName);
        }
    }

    protected function calculateBatchsCount($id_space) {
        $modelBatch = new BrBatch();
        $data = $modelBatch->getAll($id_space);
        foreach ($data as $d) {
            $modelBatch->updateQuantity($d["id"]);
        }
    }
    
    protected function sexage($id_space){
        
        $modelBatch = new BrBatch();
        $batchs = $modelBatch->getAll($id_space);
        
        foreach ($batchs as $b){
            
            
            $id_f_batch = $modelBatch->findByName($b["reference"] . "f");
            $id_m_batch = $modelBatch->findByName($b["reference"] . "m");
            if ( $id_f_batch != 0 && $id_m_batch != 0){
                
                $id_batch = $b["id"];
                $num_f = $modelBatch->getQantityStart($id_f_batch);
                $num_m = $modelBatch->getQantityStart($id_m_batch);
                $modelBatch->setSexage($id_batch, $num_f, $num_m, $id_m_batch, $id_f_batch);
            }
        }
    }

}

<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/AcProtocol.php';

class ProtocolsController extends CoresecureController {

    /**
     * User model object
     */
    private $protocolModel;

    public function __construct(Request $request) {
        parent::__construct($request);
        $this->protocolModel = new AcProtocol();
        $_SESSION["openedNav"] = "antibodies";
    }

    // affiche la liste des isotypes
    public function indexAction($id_space, $sortEntry) {
        if ($sortEntry == ""){
            $sortEntry = "id";
        }
        // get the user list
        $protocolesArray = $this->protocolModel->getProtocols2($id_space,$sortEntry);

        $table = new TableView();
        $table->setTitle("Protocoles");
        $headers = array(
            "anticorps" => "Anticorps",
            "no_h2p2" => "No H2P2",
            "kit" => "KIT",
            "no_proto" => "No Proto",
            "proto" => "Proto",
            "fixative" => "Fixative",
            "option_" => "Option",
            "enzyme" => "Enzyme",
            "dem" => "Dém",
            "acl_inc" => "AcI Inc",
            "linker" => "Linker",
            "inc" => "Linker Inc",
            "acll" => "acII",
            "inc2" => "acII Inc"
        );
        
        $table->addLineEditButton('protocolsedit/'.$id_space, "id");
        $table->addDeleteButton("protocolsdelete/".$id_space, "id", "no_proto");
        $tableView = $table->view($protocolesArray, $headers);
        
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $this->getLanguage(),
            'protocols' => $protocolesArray,
            'tableHtml' => $tableView
        ));
    }

    // DEPRECATED?
    public function protoref($id_space) {
        $anticorpsId = 0;
        if ($this->request->isParameterNotEmpty('actionid')) {
            $anticorpsId = $this->request->getParameter("actionid");
        }

        // get the user list
        //echo "action id = " . $anticorpsId . "<br />";
        $protocolesArray = $this->protocolModel->getProtocolsByAnticorps($id_space, $anticorpsId);


        // view
        $navBar = $this->navBar();
        $this->generateView(array(
            'navBar' => $navBar,
            'protocols' => $protocolesArray
                ), "index");
    }

    public function editAction($id_space, $id) {

        $protocol ['id'] = "";
        $protocol ['kit'] = "";
        $protocol ['no_proto'] = "";
        $protocol ['proto'] = "";
        $protocol ['fixative'] = "";
        $protocol ['option_'] = "";
        $protocol ['enzyme'] = "";
        $protocol ['dem'] = "";
        $protocol ['acl_inc'] = "";
        $protocol ['linker'] = "";
        $protocol ['inc'] = "";
        $protocol ['acll'] = "";
        $protocol ['inc2'] = "";
        $protocol ['associe'] = "";

        if ($id != 0) {
            // get isotype info
            $protocol = $this->protocolModel->getProtocol($id_space, $id);
        }

        // lists
        $modelKit = new Kit();
        $modelProto = new Proto();
        $modelFixative = new Fixative();
        $modelOption = new AcOption();
        $modelEnzyme = new Enzyme();
        $modelDem = new Dem();
        $modelAciinc = new Aciinc();
        $modelLinker = new Linker();
        $modelInc = new Inc();
        $modelAcii = new Acii();

        $kits = $modelKit->getKits($id_space,"id");
        $protos = $modelProto->getProtos($id_space,"id");
        $fixatives = $modelFixative->getFixatives($id_space,"id");
        $options = $modelOption->getOptions($id_space,"id");
        $enzymes = $modelEnzyme->getEnzymes($id_space,"id");
        $dems = $modelDem->getDems($id_space,"id");
        $aciincs = $modelAciinc->getAciincs($id_space,"id");
        $linkers = $modelLinker->getLinkers($id_space,"id");
        $incs = $modelInc->getIncs($id_space,"id");
        $aciis = $modelAcii->getAciis($id_space,"id");

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $id_space,
            'protocol' => $protocol,
            'kits' => $kits,
            'protos' => $protos,
            'fixatives' => $fixatives,
            'options' => $options,
            'enzymes' => $enzymes,
            'dems' => $dems,
            'aciincs' => $aciincs,
            'linkers' => $linkers,
            'incs' => $incs,
            'aciis' => $aciis
        ));
    }

    public function editqueryAction($id_space) {

        // get form variables
        $id = $this->request->getParameterNoException("id");
        $kit = $this->request->getParameter("kit");
        $no_proto = $this->request->getParameter("no_proto");
        $proto = $this->request->getParameter("proto");
        $fixative = $this->request->getParameter("fixative");
        $option = $this->request->getParameter("option");
        $enzyme = $this->request->getParameter("enzyme");
        $dem = $this->request->getParameter("dem");
        $acl_inc = $this->request->getParameter("acl_inc");
        $linker = $this->request->getParameter("linker");
        $inc = $this->request->getParameter("inc");
        $acll = $this->request->getParameter("acll");
        $inc2 = $this->request->getParameter("inc2");
        $associe = $this->request->getParameter("associate");

        // add query
        if ($id == "") {
            $this->protocolModel->addProtocol($id_space, $kit, $no_proto, $proto, $fixative, $option, $enzyme, $dem, $acl_inc, $linker, $inc, $acll, $inc2, $associe);
        } else {
            $this->protocolModel->editProtocol($id, $id_space, $kit, $no_proto, $proto, $fixative, $option, $enzyme, $dem, $acl_inc, $linker, $inc, $acll, $inc2, $associe);
        }

        $this->redirect("protocols/".$id_space. "/id");
    }

    public function deleteAction($id_space, $id) {

        // get source info
        $this->protocolModel->delete($id_space,$id);

        $this->redirect("protocols/".$id_space. "/id");
    }

}

<?php

require_once 'Framework/Controller.php';
require_once 'Framework/TableView.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/antibodies/Model/AcProtocol.php';
require_once 'Modules/antibodies/Controller/AntibodiesController.php';

class ProtocolsController extends AntibodiesController
{
    /**
     * User model object
     */
    private $protocolModel;

    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->protocolModel = new AcProtocol();
    }

    // affiche la liste des isotypes
    public function indexAction($idSpace, $sortEntry)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        if ($sortEntry == "") {
            $sortEntry = "id";
        }
        // get the user list
        $protocolesArray = $this->protocolModel->getProtocols2($idSpace, $sortEntry);

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

        $table->addLineEditButton('protocolsedit/'.$idSpace, "id");
        $table->addDeleteButton("protocolsdelete/".$idSpace, "id", "no_proto");
        $tableView = $table->view($protocolesArray, $headers);

        $this->render(array(
            'id_space' => $idSpace,
            'lang' => $this->getLanguage(),
            'protocols' => $protocolesArray,
            'tableHtml' => $tableView
        ));
    }

    // DEPRECATED?
    /**
     * @deprecated
     */

    public function protoref($idSpace)
    {
        throw new PfmException("deprecated method", 500);

        /*
        $anticorpsId = 0;
        if ($this->request->isParameterNotEmpty('actionid')) {
            $anticorpsId = $this->request->getParameter("actionid");
        }

        // get the user list
        //echo "action id = " . $anticorpsId . "<br />";
        $protocolesArray = $this->protocolModel->getProtocolsByAnticorps($idSpace, $anticorpsId);

        throw new PfmException("deprecated method", 500)
        // view
        $navBar = $this->navBar($idSpace);
        $this->generateView(array(
            'navBar' => $navBar,
            'protocols' => $protocolesArray
                ), "index");
        */
    }

    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
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
            $protocol = $this->protocolModel->getProtocol($idSpace, $id);
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

        $kits = $modelKit->getKits($idSpace, "id");
        $protos = $modelProto->getProtos($idSpace, "id");
        $fixatives = $modelFixative->getFixatives($idSpace, "id");
        $options = $modelOption->getOptions($idSpace, "id");
        $enzymes = $modelEnzyme->getEnzymes($idSpace, "id");
        $dems = $modelDem->getDems($idSpace, "id");
        $aciincs = $modelAciinc->getAciincs($idSpace, "id");
        $linkers = $modelLinker->getLinkers($idSpace, "id");
        $incs = $modelInc->getIncs($idSpace, "id");
        $aciis = $modelAcii->getAciis($idSpace, "id");

        $this->render(array(
            'lang' => $this->getLanguage(),
            'id_space' => $idSpace,
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

    public function editqueryAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
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
            $id = $this->protocolModel->addProtocol($idSpace, $kit, $no_proto, $proto, $fixative, $option, $enzyme, $dem, $acl_inc, $linker, $inc, $acll, $inc2, $associe);
        } else {
            $this->protocolModel->editProtocol($id, $idSpace, $kit, $no_proto, $proto, $fixative, $option, $enzyme, $dem, $acl_inc, $linker, $inc, $acll, $inc2, $associe);
        }

        return $this->redirect("protocols/".$idSpace. "/id", [], ['protocol' => ['id' => $id]]);
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("antibodies", $idSpace, $_SESSION["id_user"]);
        // get source info
        $this->protocolModel->delete($idSpace, $id);

        $this->redirect("protocols/".$idSpace. "/id");
    }
}

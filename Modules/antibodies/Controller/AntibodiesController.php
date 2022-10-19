<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Controller/CorespaceController.php';

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

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class AntibodiesController extends CoresecureController
{
    private $antibody;
    protected $noSideMenu = false;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->antibody = new Anticorps();
    }

    public function dropdownMenu($idSpace)
    {
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("antibodies", $idSpace);

        $dataView = [
            'id_space' => $idSpace,
            'title' => AntibodiesTranslator::antibodies($lang),
            'glyphicon' => $menuInfo['icon'],
            'bgcolor' => $menuInfo['color'],
            'color' => $menuInfo['txtcolor'] ?? ''
        ];
        return $this->twig->render("Modules/antibodies/View/navbar.twig", $dataView);
    }

    public function sideMenu()
    {
        if ($this->noSideMenu) {
            return null;
        }
        $idSpace = $this->args['id_space'];
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("antibodies", $idSpace);

        $dataView = [
            'id_space' => $idSpace,
            'title' => AntibodiesTranslator::antibodies($lang),
            'glyphicon' => $menuInfo['icon'],
            'bgcolor' => $menuInfo['color'],
            'color' => $menuInfo['txtcolor'] ?? ''
        ];
        return $this->twig->render("Modules/antibodies/View/Antibodies/navbar.twig", $dataView);
    }

    public function navbar($idSpace)
    {
        $lang = $this->getLanguage();

        $html = file_get_contents('Modules/antibodies/View/Antibodies/navbar.php');
        $html = str_replace('{{id_space}}', $idSpace, $html);

        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("antibodies", $idSpace);

        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', AntibodiesTranslator::antibodies($lang), $html);

        return $html;
    }
}

<?php
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Controller/CorespaceController.php';
require_once 'Modules/core/Model/CoreSpace.php';


class ResourcesBaseController extends CoresecureController {


    public function sideMenu() {
        $id_space = $this->args['id_space'];
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("resources", $id_space);

        $dataView = [
            'id_space' => $id_space,
            'title' => ResourcesTranslator::Resources($lang),
            'glyphicon' => $menuInfo['icon'],
            'bgcolor' => $menuInfo['color'],
            'color' => $menuInfo['txtcolor'] ?? '',
            'Resources' => ResourcesTranslator::Resources($lang),
            'Sorting' => ResourcesTranslator::Sorting($lang),
            'Areas' => ResourcesTranslator::Areas($lang),
            'Categories' => ResourcesTranslator::Categories($lang),
            'Responsible' => ResourcesTranslator::Responsible($lang),
            'Resps_Status' => ResourcesTranslator::Resps_Status($lang),
            'Visas' => ResourcesTranslator::Visas($lang),
            'Suivi' => ResourcesTranslator::Suivi($lang),
            'States' => ResourcesTranslator::States($lang),
            'Event_Types' => ResourcesTranslator::Event_Types($lang),

        ];
        return $this->twig->render("Modules/resources/View/Resources/navbar.twig", $dataView);

    }

}

?>
<?php
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Controller/CorespaceController.php';
require_once 'Modules/core/Model/CoreSpace.php';


class ResourcesBaseController extends CoresecureController {

    public function mainMenu() {
        $id_space = isset($this->args['id_space']) ? $this->args['id_space'] : null;
        if ($id_space) {
            $csc = new CoreSpaceController($this->request);
            return $csc->navbar($id_space);
        }
        return null;
    }

    public function sideMenu() {
        $id_space = $this->args['id_space'];
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("resources", $id_space);
        /*
        $html = file_get_contents('Modules/resources/View/Resources/navbar.php');
        $html = str_replace('{{id_space}}', $id_space, $html);
        $html = str_replace('{{Resources}}', ResourcesTranslator::Resources($lang), $html);
        $html = str_replace('{{Sorting}}', ResourcesTranslator::Sorting($lang), $html);
        $html = str_replace('{{Areas}}', ResourcesTranslator::Areas($lang), $html);
        $html = str_replace('{{Categories}}', ResourcesTranslator::Categories($lang), $html);
        $html = str_replace('{{Responsible}}', ResourcesTranslator::Responsible($lang), $html);
        $html = str_replace('{{Resps_Status}}', ResourcesTranslator::Resps_Status($lang), $html);
        $html = str_replace('{{Visas}}', ResourcesTranslator::Visas($lang), $html);
        $html = str_replace('{{Suivi}}', ResourcesTranslator::Suivi($lang), $html);
        $html = str_replace('{{States}}', ResourcesTranslator::States($lang), $html);
        $html = str_replace('{{Event_Types}}', ResourcesTranslator::Event_Types($lang), $html);
        

        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', ResourcesTranslator::Resources($lang), $html);
        return $html;
        */
        
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
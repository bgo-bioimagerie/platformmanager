<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/com/Model/ComTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';
require_once 'Modules/core/Model/CoreSpace.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class ComController extends CoresecureController
{
    public function sideMenu()
    {
        $idSpace = $this->args['id_space'];
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("com", $idSpace);

        $dataView = [
            'id_space' => $idSpace,
            'title' =>  ComTranslator::Com($lang),
            'glyphicon' => $menuInfo['icon'],
            'bgcolor' => $menuInfo['color'],
            'color' => $menuInfo['txtcolor'] ?? '',
            'Tilemessage' => ComTranslator::Tilemessage($lang),
            'News' => ComTranslator::News($lang),
            'isAdmin' => $this->role == CoreSpace::$ADMIN
        ];
        return $this->twig->render("Modules/com/View/Com/navbar.twig", $dataView);
    }

    public function indexAction($idSpace)
    {
        if ($this->role  && $this->role == CoreSpace::$ADMIN) {
            return $this->redirect('comtileedit/' . $idSpace);
        }
        return $this->redirect('comnews/'.$idSpace);
    }

    public function navbar($idSpace)
    {
        $html = file_get_contents('Modules/com/View/Com/navbar.php');

        $lang = $this->getLanguage();
        $html = str_replace('{{id_space}}', $idSpace, $html);
        $html = str_replace('{{Tilemessage}}', ComTranslator::Tilemessage($lang), $html);
        $html = str_replace('{{News}}', ComTranslator::News($lang), $html);

        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("com", $idSpace);
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', ComTranslator::Com($lang), $html);

        return $html;
    }
}

<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/users/Model/UsersTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class UsersController extends CoresecureController {

    public function sideMenu() {
        $id_space = $this->args['id_space'];
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("users", $id_space);
        
        $dataView = [
            'id_space' => $id_space,
            'title' =>  ComTranslator::Com($lang),
            'glyphicon' => $menuInfo['icon'],
            'bgcolor' => $menuInfo['color'],
            'color' => $menuInfo['txtcolor'] ?? ''
        ];
        return $this->twig->render("Modules/users/View/Users/navbar.twig", $dataView);
    }

    public function navbar($id_space){
        
        $lang = $this->getLanguage();
        
        $html = file_get_contents('Modules/users/View/Users/navbar.php');
        
        $html = str_replace('{{id_space}}', $id_space, $html);
        //$html = str_replace('{{Providers}}', UsersTranslator::Providers($lang), $html);
        //$html = str_replace('{{NewProvider}}', UsersTranslator::NewProvider($lang), $html);
        
        return $html;
    }
}

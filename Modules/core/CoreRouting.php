<?php

require_once 'Framework/Routing.php';

class CoreRouting extends Routing{

    public function routes($router) {
        $router->map('GET', '/corefiles/[i:id_space]/[i:id_file]', 'core/corefiles/download', 'files_download');
        $router->map('GET', '/corespaceaccess/[i:id_space]/user/expire', 'core/corespaceaccess/expire', 'userspace_expire');
        $router->map('GET', '/corespaceaccess/[i:id_space]/user/expire/run', 'core/corespaceaccess/doexpire', 'userspace_expire_run');
        $router->map('GET', '/corespaceaccess/[i:id_space]/impersonate/[i:id_user]', 'core/corespaceaccess/impersonate', 'user_impersonate');
        $router->map('GET', '/corespaceaccess/[i:id_space]/unimpersonate', 'core/corespaceaccess/unimpersonate', 'user_unimpersonate');
        $router->map('GET', '/corecreateaccountconfirm', 'core/coreaccount/confirm', 'core_createaccount_confirm');
        $router->map('GET', '/coreuserwaiting', 'core/coreaccount/waiting', 'core_createaccount_waiting');
        $router->map('GET', '/corespaces', 'core/corespace/spaces', 'core_space_list');
        $router->map('GET', '/core/about', 'core/coreabout/index', 'core_about');
        $router->map('GET', '/coretiles/[i:level]/[i:id]/star/[i:id_space]', 'core/coretiles/corestar', 'core_star');
        $router->map('GET', '/coretiles/[i:level]/[i:id]/unstar/[i:id_space]', 'core/coretiles/coreunstar', 'core_unstar');
        $router->map('GET|POST', '/coremail/[i:id_space]', 'core/coremail/index', 'core_mail');
        $router->map('GET|POST', '/coreaccountisunique', 'core/coreaccount/isunique', 'core_unique');
        $router->map('GET', '/core/tiles/[i:id_space]/module/spaceaccess/notifs', 'core/corespaceaccess/notifs', 'corespaceaccess_notifs');
        $router->map('GET', '/core/plans', 'core/coreabout/plans', 'core_plans');
        $router->map('GET', '/core/spaceaccess/[i:id_space]/users/[i:id_user]/convention', 'core/corespaceaccess/downloadConvention', 'corespaceaccess_download_user_convention');
        $router->map('GET', '/core/privacy', 'core/coreabout/privacy', 'core_privacy');

        $router->map( 'GET', '/ooc/[a:provider]/authorized', 'core/openid/connect', 'ooc' );
        $router->map( 'GET', '/ooc/[a:provider]/unlink', 'core/openid/unlink', 'ooc_unlink' );
        $router->map( 'GET|POST', '/ooc/shibboleth', 'core/shibboleth/connect', 'shibboleth' );

    }
    
    public function listRoutes(){
        
        // config
        $this->addRoute("coreconfigadmin", "coreconfigadmin", "coreconfigadmin", "index");
        //$this->addRoute("coreldapconfig", "coreldapconfig", "coreldapconfig", "index");
        
        // connection
        $this->addRoute("coreconnection", "coreconnection", "coreconnection", "index");
        $this->addRoute("corelogin", "corelogin", "coreconnection", "login");
        $this->addRoute("corelogout", "corelogout", "coreconnection", "logout");
        $this->addRoute("corepasswordforgotten", "corepasswordforgotten", "coreconnection", "passwordforgotten");
        
        // create account
        $this->addRoute("corecreateaccount", "corecreateaccount", "coreaccount", "index");
        $this->addRoute("coreaccountcreated", "coreaccountcreated", "coreaccount", "created");
         
        // home
        $this->addRoute("corehome", "corehome", "corehome", "index");
        
        // tiles
        $this->addRoute("coretiles", "coretiles", "coretiles", "index", array("level", "id"), array("", ""));
        $this->addRoute("coretilesdoc", "coretilesdoc", "coretiles", "doc");
        $this->addRoute("coretilesselfjoinspace", "coretilesselfjoinspace", "coretiles", "selfjoinspace", array("id_space"), array(""));
        
        // Users
        $this->addRoute("coreusers", "coreusers", "coreusers", "index");
        $this->addRoute("coreusersedit", "coreusersedit", "coreusers", "edit", array("id"), array(""));
        $this->addRoute("coreusersdelete", "coreusersdelete", "coreusers", "delete", array("id"), array(""));
        $this->addRoute("coreuserslanguageedit", "coreuserslanguageedit", "coreusers", "languageedit");

        // settings
        $this->addRoute("coremyaccount", "coremyaccount", "coreusers", "myaccount");
        $this->addRoute("coresettings", "coresettings", "coresettings", "index");
        
        // spaces
        $this->addRoute("corespace", "corespace", "corespace", "view", array("id_space"), array(""));
        $this->addRoute("spaceconfig", "spaceconfig", "corespace", "config", array("id_space"), array(""));
        $this->addRoute("spaceconfiguser", "spaceconfiguser", "corespace", "configusers", array("id_space"), array(""));
        $this->addRoute("spaceconfigmodule", "spaceconfigmodule", "corespace", "configmodule", array("id_space", "name_module"), array("", ""));
        
        // space access
        $this->addRoute("corespaceaccess", "corespaceaccess", "corespaceaccess", "index", array("id_space", "letter", "active"), array("", "", ""));
        $this->addRoute("corespacependingusers", "corespacependingusers", "corespaceaccess", "pendingusers", array("id_space"), array(""));
        $this->addRoute("corespacependinguseredit", "corespacependinguseredit", "corespaceaccess", "pendinguseredit", array("id_space", "id"), array("", ""));
        $this->addRoute("corespaceaccessusers", "corespaceaccessusers", "corespaceaccess", "users", array("id_space", "letter"), array("", ""));
        $this->addRoute("corespaceaccessusersinactifs", "corespaceaccessusersinactifs", "corespaceaccess", "usersinactif", array("id_space", "letter"), array("", ""));
        $this->addRoute("corespaceaccessuseradd", "corespaceaccessuseradd", "corespaceaccess", "useradd", array("id_space"), array(""));
        $this->addRoute("spaceconfigdeleteuser", "spaceconfigdeleteuser", "corespace", "configdeleteuser", array("id_space", "id_user"), array("", ""));
        $this->addRoute("corespacependinguserdelete", "corespacependinguserdelete", "corespaceaccess", "pendinguserdelete", array("id_space", "id"), array("", ""));
        $this->addRoute("corespaceuserdelete", "corespaceuserdelete", "corespaceaccess", "userdelete", array("id_space", "id_user"), array("", ""));
        
        // space user edit
        $this->addRoute("corespaceuseredit", "corespaceuseredit", "corespaceuser", "edit", array("id_space", "id_user"), array("", ""));
        $this->addRoute("corespaceuserdeleteclientsuser", "corespaceuserdeleteclientsuser", "corespaceuser", "deleteclientsuser", array("id_space", "id_user", "id_client"), array("", "", ""));
        $this->addRoute("corespaceuserbkauthAdd", "corespaceuserbkauthadd", "corespaceuser", "bkauthadd", array("id_space", "id_user"), array("", ""));
        $this->addRoute("corespaceuseredithist", "corespaceuseredithist", "corespaceuser", "edit", array("id_space", "id_user"), array("", ""));

        // history
        $this->addRoute("corespacehistory", "corespacehistory", "corespacehistory", "index", array("id_space"), array(""));
        
        // spaces admin
        $this->addRoute("spaceadmin", "spaceadmin", "corespaceadmin", "index");
        $this->addRoute("spaceadminedit", "spaceadminedit", "corespaceadmin", "edit", array("id_space"), array(""));
        $this->addRoute("spaceadmindelete", "spaceadmindelete", "corespaceadmin", "delete", array("id_space"), array(""));
        
        // main menu
        $this->addRoute("coremainmenus", "coremainmenus", "coremainmenu", "index");
        $this->addRoute("coremainmenuedit", "coremainmenuedit", "coremainmenu", "edit", array("id"), array(""));
        $this->addRoute("coremainmenudelete", "coremainmenudelete", "coremainmenu", "delete", array("id"), array(""));
        
        $this->addRoute("coremainsubmenus", "coremainsubmenus", "coremainmenu", "submenus");
        $this->addRoute("coremainsubmenuedit", "coremainsubmenuedit", "coremainmenu", "submenuedit", array("id"), array(""));
        $this->addRoute("coremainsubmenudelete", "coremainsubmenudelete", "coremainmenu", "submenudelete", array("id"), array(""));
        
        $this->addRoute("coremainmenuitems", "coremainmenuitems", "coremainmenu", "items");
        $this->addRoute("coremainmenuitemedit", "coremainmenuitemedit", "coremainmenu", "itemedit", array("id"), array(""));
        $this->addRoute("coremainmenuitemdelete", "coremainmenuitemdelete", "coremainmenu", "itemdelete", array("id"), array(""));

        
        // api
        $this->addRoute("apinavbar", "apinavbar", "corenavbar", "navbar", array(), array(), true);
        $this->addRoute("apilogin", "apilogin", "corelogin", "login", array(), array(), true);
        $this->addRoute("apiping", "apiping", "coreping", "ping", array(), array(), true);
        
        
    }
}

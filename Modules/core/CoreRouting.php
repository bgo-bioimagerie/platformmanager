<?php

require_once 'Framework/Routing.php';

class CoreRouting extends Routing{

    public function routes($router) {
        $router->map('GET', '/corefiles/[i:id_space]/[i:id_file]', 'core/corefiles/download', 'files_download');
        $router->map('GET', '/corespaceaccess/[i:id_space]/impersonate/[i:id_user]', 'core/corespaceaccess/impersonate', 'user_impersonate');
        $router->map('GET', '/corespaceaccess/[i:id_space]/unimpersonate', 'core/corespaceaccess/unimpersonate', 'user_unimpersonate');
        $router->map('GET', '/corecreateaccountconfirm', 'core/coreaccount/confirm', 'core_createaccount_confirm');
        $router->map('GET', '/coreuserwaiting', 'core/coreaccount/waiting', 'core_createaccount_waiting');
        $router->map('GET', '/corespaces', 'core/corespace/spaces', 'core_space_list');
        $router->map('GET', '/coreabout', 'core/coreabout/index', 'core_about');
        $router->map('GET|POST', '/coremail/[i:id_space]', 'core/coremail/index', 'core_mail');
        $router->map('GET|POST', '/coreaccountisunique', 'core/coreaccount/isunique', 'core_unique');
    }
    
    public function listRoutes(){
        
        // config
        $this->addRoute("coreconfigadmin", "coreconfigadmin", "coreconfigadmin", "index");
        $this->addRoute("coreldapconfig", "coreldapconfig", "coreldapconfig", "index");
        
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
        
        
        // Update
        $this->addRoute("update", "update", "coreupdate", "update");
        $this->addRoute("coreupdate", "coreupdate", "coreupdate", "index");
        
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
        $this->addRoute("coreaccessuseredit", "coreaccessuseredit", "corespaceaccess", "useredit", array("id_space", "id"), array("", ""));
        $this->addRoute("corespaceaccessuseradd", "corespaceaccessuseradd", "corespaceaccess", "useradd", array("id_space"), array(""));
        $this->addRoute("spaceconfigdeleteuser", "spaceconfigdeleteuser", "corespace", "configdeleteuser", array("id_space", "id_user"), array("", ""));
        // multi-tenant feature: route for rejecting user requesting to join space
        $this->addRoute("corespacependinguserdelete", "corespacependinguserdelete", "corespaceaccess", "pendinguserdelete", array("id_space", "id"), array("", ""));
        $this->addRoute("corespaceuserdelete", "corespaceuserdelete", "corespaceaccess", "userdelete", array("id_space", "id_user"), array("", ""));
    
        // history
        $this->addRoute("corespacehistory", "corespacehistory", "corespacehistory", "index", array("id_space"), array(""));
        
        // spaces admin
        $this->addRoute("spaceadmin", "spaceadmin", "corespaceadmin", "index");
        $this->addRoute("spaceadminedit", "spaceadminedit", "corespaceadmin", "edit", array("id"), array(""));
        $this->addRoute("spaceadmindelete", "spaceadmindelete", "corespaceadmin", "delete", array("id"), array(""));
        
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

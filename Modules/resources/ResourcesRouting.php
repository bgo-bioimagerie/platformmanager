<?php

require_once 'Framework/Routing.php';

class ResourcesRouting extends Routing
{
    public function routes($router)
    {
        $router->map('GET', '/resources/eventfile/[i:id_space]/[i:id]', 'resources/resourcesinfo/downloadeventfile', 'resources_downloadeventfileAction');
        $router->map(
            'GET|POST',
            '/revisas/getcategoryvisas/[i:id_space]/[i:id_category]',
            'resources/revisas/getcategoryvisas',
            'resources_getcategoryvisas'
        );
    }


    public function listRoutes()
    {
        // config
        $this->addRoute("resourcesconfig", "resourcesconfig", "resourcesconfig", "index", array("id_space"), array(""));
        $this->addRoute("resourceshelp", "resourceshelp", "resourceshelp", "index", array("id_space"), array(""));


        // add here the module routes
        $this->addRoute("resources", "resources", "resourcesinfo", "index", array("id_space"), array(""));
        $this->addRoute("resourcesedit", "resourcesedit", "resourcesinfo", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("resourcesevents", "resourcesevents", "resourcesinfo", "events", array("id_space","id"), array("", ""));
        $this->addRoute("resourceseventsro", "resourceseventsro", "resourcesinfo", "eventsro", array("id_space","id"), array("", ""));
        $this->addRoute("resourceseventdelete", "resourceseventdelete", "resourcesinfo", "deleteevent", array("id_space", "id_resource", "id"), array("", "", ""));

        $this->addRoute("resourceeditevent", "resourceeditevent", "resourcesinfo", "editevent", array("id_space", "id_resource", "id_event"), array("", "", ""));
        $this->addRoute("resourceediteventro", "resourceediteventro", "resourcesinfo", "editeventro", array("id_space", "id_resource", "id_event"), array("", "", ""));
        $this->addRoute("resourceediteventfile", "resourceediteventfile", "resourcesinfo", "editeventfile", array("id_space"), array(""));
        $this->addRoute("resourcesresp", "resourcesresp", "resourcesinfo", "resps", array("id_space", "id_resource"), array("", ""));
        $this->addRoute("resourcesdelete", "resourcesdelete", "resourcesinfo", "delete", array("id_space", "id"), array("", ""));

        $this->addRoute("reareas", "reareas", "reareas", "index", array("id_space"), array(""));
        $this->addRoute("reareasedit", "reareasedit", "reareas", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("reareasdelete", "reareasdelete", "reareas", "delete", array("id_space", "id"), array("", ""));

        $this->addRoute("rerespsstatus", "rerespsstatus", "rerespsstatus", "index", array("id_space"), array(""));
        $this->addRoute("rerespsstatusedit", "rerespsstatusedit", "rerespsstatus", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("rerespsstatusdelete", "rerespsstatusdelete", "rerespsstatus", "delete", array("id_space", "id"), array("", ""));

        $this->addRoute("recategories", "recategories", "recategories", "index", array("id_space"), array(""));
        $this->addRoute("recategoriesedit", "recategoriesedit", "recategories", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("recategoriesdelete", "recategoriesdelete", "recategories", "delete", array("id_space", "id"), array("", ""));

        $this->addRoute("restates", "restates", "restates", "index", array("id_space"), array(""));
        $this->addRoute("restatesedit", "restatesedit", "restates", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("restatesdelete", "restatesdelete", "restates", "delete", array("id_space", "id"), array("", ""));

        $this->addRoute("reeventtypes", "reeventtypes", "reeventtypes", "index", array("id_space"), array(""));
        $this->addRoute("reeventtypesedit", "reeventtypesedit", "reeventtypes", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("reeventtypesdelete", "reeventtypesdelete", "reeventtypes", "delete", array("id_space", "id"), array("", ""));

        $this->addRoute("resourcesvisa", "resourcesvisa", "revisas", "index", array("id_space"), array(""));
        $this->addRoute("resourceseditvisa", "resourceseditvisa", "revisas", "edit", array("id_space", "id"), array("", ""));
        $this->addRoute("resourcesdeletevisa", "resourcesdeletevisa", "revisas", "delete", array("id_space", "id"), array("", ""));
        $this->addRoute("resourcesexportvisa", "resourcesexportvisa", "revisas", "export", array("id_space"), array(""));
    }
}

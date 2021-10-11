<?php

require_once 'Framework/Routing.php';

class HelpdeskRouting extends Routing{

    public function routes($router) {
        $router->map('GET', '/helpdesk/[i:id_space]', 'helpdesk/helpdesk/index', 'helpdesk_index');
        $router->map('GET', '/helpdesk/[i:id_space]/settings', 'helpdesk/helpdesk/settings', 'helpdesk_settings');
        $router->map('GET', '/helpdesk/[i:id_space]/unread', 'helpdesk/helpdesk/unreadCount', 'helpdesk_unread_count');
        $router->map('POST', '/helpdesk/[i:id_space]/settings', 'helpdesk/helpdesk/setSettings', 'helpdesk_set_settings');
        $router->map('GET', '/helpdesk/[i:id_space]/[i:id_ticket]', 'helpdesk/helpdesk/messages', 'helpdesk_ticket_messages');
        $router->map('POST', '/helpdesk/[i:id_space]/[i:id_ticket]/status/[i:status]', 'helpdesk/helpdesk/status', 'helpdesk_ticket_status');
        $router->map('POST', '/helpdesk/[i:id_space]/[i:id_ticket]', 'helpdesk/helpdesk/message', 'helpdesk_ticket_add_message');
        $router->map('POST', '/helpdesk/[i:id_space]/[i:id_ticket]/assign', 'helpdesk/helpdesk/assign', 'helpdesk_ticket_assign');
        $router->map('GET', '/helpdesk/[i:id_space]/list/[i:status]', 'helpdesk/helpdesk/list', 'helpdesk_list');
        $router->map('GET|POST', '/helpdeskconfig/[i:id_space]', 'helpdesk/helpdeskconfig/index', 'helpdesk_config');
    }

    /**
     * Empty but needed as abstract from Routing
     */
    public function listRoutes() {

    }

}

?>

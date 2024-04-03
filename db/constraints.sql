-- À décommenter si nécessaire (voir reset en bas de fichier)
-- SET sql_mode = '';

-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR core_config

ALTER TABLE core_config ADD CONSTRAINT core_config_unique UNIQUE (keyname, id_space);

-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR core_spaces.id

ALTER TABLE ac_aciincs ADD CONSTRAINT ac_aciincs_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE ac_aciis ADD CONSTRAINT ac_aciis_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE ac_anticorps ADD CONSTRAINT ac_anticorps_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE ac_applications ADD CONSTRAINT ac_applications_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE ac_dems ADD CONSTRAINT ac_dems_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE ac_enzymes ADD CONSTRAINT ac_enzymes_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE ac_especes ADD CONSTRAINT ac_especes_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE ac_fixatives ADD CONSTRAINT ac_fixatives_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE ac_incs ADD CONSTRAINT ac_incs_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE ac_isotypes ADD CONSTRAINT ac_isotypes_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE ac_j_tissu_anticorps ADD CONSTRAINT ac_j_tissu_anticorps_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE ac_j_user_anticorps ADD CONSTRAINT ac_j_user_anticorps_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE ac_kits ADD CONSTRAINT ac_kits_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE ac_linkers ADD CONSTRAINT ac_linkers_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE ac_options ADD CONSTRAINT ac_options_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE ac_organes ADD CONSTRAINT ac_organes_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE ac_prelevements ADD CONSTRAINT ac_prelevements_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE ac_protocol ADD CONSTRAINT ac_protocol_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE ac_protos ADD CONSTRAINT ac_protos_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE ac_sources ADD CONSTRAINT ac_sources_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE ac_stainings ADD CONSTRAINT ac_stainings_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE ac_status ADD CONSTRAINT ac_status_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE bk_access ADD CONSTRAINT bk_access_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE bk_authorization ADD CONSTRAINT bk_authorization_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE bk_booking_settings ADD CONSTRAINT bk_booking_settings_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE bk_bookingcss ADD CONSTRAINT bk_bookingcss_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE bk_calendar_entry ADD CONSTRAINT bk_calendar_entry_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE bk_calendar_period ADD CONSTRAINT bk_calendar_period_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE bk_calquantities ADD CONSTRAINT bk_calquantities_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE bk_calsupinfo ADD CONSTRAINT bk_calsupinfo_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE bk_color_codes ADD CONSTRAINT bk_color_codes_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE bk_j_packages_prices ADD CONSTRAINT bk_j_packages_prices_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE bk_nightwe ADD CONSTRAINT bk_nightwe_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE bk_owner_prices ADD CONSTRAINT bk_owner_prices_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE bk_packages ADD CONSTRAINT bk_packages_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE bk_prices ADD CONSTRAINT bk_prices_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE bk_restrictions ADD CONSTRAINT bk_restrictions_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE bk_schedulings ADD CONSTRAINT bk_schedulings_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE ca_categories ADD CONSTRAINT ca_categories_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE ca_entries ADD CONSTRAINT ca_entries_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE cl_addresses ADD CONSTRAINT cl_addresses_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE cl_clients ADD CONSTRAINT cl_clients_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE cl_company ADD CONSTRAINT cl_company_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE cl_j_client_user ADD CONSTRAINT cl_j_client_user_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE cl_pricings ADD CONSTRAINT cl_pricings_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE com_news ADD CONSTRAINT com_news_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE core_config ADD CONSTRAINT core_config_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE core_files ADD CONSTRAINT core_files_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE core_history ADD CONSTRAINT core_history_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE core_j_spaces_user ADD CONSTRAINT core_j_spaces_user_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE core_mail_unsubscribe ADD CONSTRAINT core_mail_unsubscribe_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE core_main_menu_items ADD CONSTRAINT core_main_menu_items_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE core_pending_accounts ADD CONSTRAINT core_pending_accounts_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE core_projects ADD CONSTRAINT core_projects_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);
 
ALTER TABLE core_space_access_options ADD CONSTRAINT core_space_access_options_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE core_space_menus ADD CONSTRAINT core_space_menus_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE core_star ADD CONSTRAINT core_star_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE core_user_space_settings ADD CONSTRAINT core_user_space_settings_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE dc_documents ADD CONSTRAINT dc_documents_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE hp_tickets ADD CONSTRAINT hp_tickets_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE in_invoice ADD CONSTRAINT in_invoice_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE in_invoice_item ADD CONSTRAINT in_invoice_item_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE in_visa ADD CONSTRAINT in_visa_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE mailer_mails ADD CONSTRAINT mailer_mails_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE qo_quoteitems ADD CONSTRAINT qo_quoteitems_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE qo_quotes ADD CONSTRAINT qo_quotes_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE rating ADD CONSTRAINT rating_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE rating_campaign ADD CONSTRAINT rating_campaign_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE re_area ADD CONSTRAINT re_area_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE re_category ADD CONSTRAINT re_category_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE re_event ADD CONSTRAINT re_event_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE re_event_data ADD CONSTRAINT re_event_data_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE re_event_type ADD CONSTRAINT re_event_type_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE re_info ADD CONSTRAINT re_info_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE re_resps ADD CONSTRAINT re_resps_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE re_resps_status ADD CONSTRAINT re_resps_status_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE re_state ADD CONSTRAINT re_state_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE re_visas ADD CONSTRAINT re_visas_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE se_order ADD CONSTRAINT se_order_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE se_order_service ADD CONSTRAINT se_order_service_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE se_origin ADD CONSTRAINT se_origin_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE se_prices ADD CONSTRAINT se_prices_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE se_project ADD CONSTRAINT se_project_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE se_project_service ADD CONSTRAINT se_project_service_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE se_project_user ADD CONSTRAINT se_project_user_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE se_purchase ADD CONSTRAINT se_purchase_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE se_purchase_item ADD CONSTRAINT se_purchase_item_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE se_services ADD CONSTRAINT se_services_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE se_task ADD CONSTRAINT se_task_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE se_task_category ADD CONSTRAINT se_task_category_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE se_task_service ADD CONSTRAINT se_task_service_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE se_visa ADD CONSTRAINT se_visa_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE stock_cabinets ADD CONSTRAINT stock_cabinets_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

ALTER TABLE stock_shelf ADD CONSTRAINT stock_shelf_FK FOREIGN KEY (id_space) REFERENCES core_spaces(id);

-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR core_users.id

-- TODO: revoir le lien entre core_files#user et core_users#login

-- TODO: revoir les liens entre in_invoice#id_responsible et core_users#id d'une part, cl_clients#id d'autre part (cf. InInvoice.php lignes 151-200)

ALTER TABLE core_users_settings ADD CONSTRAINT core_users_settings_FK FOREIGN KEY (user_id) REFERENCES core_users(id);

ALTER TABLE core_user_space_settings ADD CONSTRAINT core_user_space_settings_FK_1 FOREIGN KEY (user_id) REFERENCES core_users(id);

ALTER TABLE bk_authorization ADD CONSTRAINT bk_authorization_FK_1 FOREIGN KEY (user_id) REFERENCES core_users(id);

ALTER TABLE cl_j_client_user ADD CONSTRAINT cl_j_client_user_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE core_files ADD CONSTRAINT core_files_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE core_j_spaces_user ADD CONSTRAINT core_j_spaces_user_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE core_mail_unsubscribe ADD CONSTRAINT core_mail_unsubscribe_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE core_openid ADD CONSTRAINT core_openid_FK FOREIGN KEY (`user`) REFERENCES core_users(id);

ALTER TABLE core_pending_accounts ADD CONSTRAINT core_pending_accounts_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE core_star ADD CONSTRAINT core_star_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE dc_documents ADD CONSTRAINT dc_documents_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE hp_tickets ADD CONSTRAINT hp_tickets_FK_1 FOREIGN KEY (created_by_user) REFERENCES core_users(id);

ALTER TABLE in_invoice ADD CONSTRAINT in_invoice_FK_1 FOREIGN KEY (id_edited_by) REFERENCES core_users(id);

ALTER TABLE in_visa ADD CONSTRAINT in_visa_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE qo_quotes ADD CONSTRAINT qo_quotes_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE rating ADD CONSTRAINT rating_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE re_event ADD CONSTRAINT re_event_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE re_resps ADD CONSTRAINT re_resps_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE re_visas ADD CONSTRAINT re_visas_FK_1 FOREIGN KEY (id_instructor) REFERENCES core_users(id);

ALTER TABLE se_order ADD CONSTRAINT se_order_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE se_project ADD CONSTRAINT se_project_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE se_project_user ADD CONSTRAINT se_project_user_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE se_task ADD CONSTRAINT se_task_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE se_task ADD CONSTRAINT se_task_FK_2 FOREIGN KEY (id_owner) REFERENCES core_users(id);

ALTER TABLE se_visa ADD CONSTRAINT se_visa_FK_1 FOREIGN KEY (id_user) REFERENCES core_users(id);

ALTER TABLE users_info ADD CONSTRAINT users_info_FK FOREIGN KEY (id_core) REFERENCES core_users(id);


-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR cl_clients.id (voir issue#500 sur github )

ALTER TABLE cl_j_client_user ADD CONSTRAINT cl_j_client_user_FK_2 FOREIGN KEY (id_client) REFERENCES cl_clients(id);

ALTER TABLE se_project ADD CONSTRAINT se_project_FK_2 FOREIGN KEY (id_resp) REFERENCES cl_clients(id);


-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR ac_*

-- TODO: revoir le lien entre ac_j_tissu_anticorps#ref_protocol et ac_protocol#no_proto ; cf. AcProtocol.php ligne 161 et suite

ALTER TABLE ac_anticorps ADD CONSTRAINT ac_anticorps_FK_1 FOREIGN KEY (id_source) REFERENCES ac_sources(id);

ALTER TABLE ac_anticorps ADD CONSTRAINT ac_anticorps_FK_2 FOREIGN KEY (id_isotype) REFERENCES ac_isotypes(id);

ALTER TABLE ac_anticorps ADD CONSTRAINT ac_anticorps_FK_3 FOREIGN KEY (id_application) REFERENCES ac_applications(id);

ALTER TABLE ac_anticorps ADD CONSTRAINT ac_anticorps_FK_4 FOREIGN KEY (id_staining) REFERENCES ac_stainings(id);

ALTER TABLE ac_j_tissu_anticorps ADD CONSTRAINT ac_j_tissu_anticorps_FK_1 FOREIGN KEY (id_anticorps) REFERENCES ac_anticorps(id);

ALTER TABLE ac_j_tissu_anticorps ADD CONSTRAINT ac_j_tissu_anticorps_FK_2 FOREIGN KEY (espece) REFERENCES ac_especes(id);

ALTER TABLE ac_j_tissu_anticorps ADD CONSTRAINT ac_j_tissu_anticorps_FK_3 FOREIGN KEY (organe) REFERENCES ac_organes(id);

ALTER TABLE ac_j_tissu_anticorps ADD CONSTRAINT ac_j_tissu_anticorps_FK_4 FOREIGN KEY (status) REFERENCES ac_status(id);

ALTER TABLE ac_j_user_anticorps ADD CONSTRAINT ac_j_user_anticorps_FK_1 FOREIGN KEY (id_anticorps) REFERENCES ac_anticorps(id);

ALTER TABLE ac_j_user_anticorps ADD CONSTRAINT ac_j_user_anticorps_FK_2 FOREIGN KEY (id_utilisateur) REFERENCES core_users(id);

ALTER TABLE ac_j_tissu_anticorps ADD CONSTRAINT ac_j_tissu_anticorps_FK_5 FOREIGN KEY (prelevement) REFERENCES ac_prelevements(id);

ALTER TABLE ac_protocol ADD CONSTRAINT ac_protocol_FK_1 FOREIGN KEY (dem) REFERENCES ac_dems(id);

ALTER TABLE ac_protocol ADD CONSTRAINT ac_protocol_FK_2 FOREIGN KEY (kit) REFERENCES ac_kits(id);

ALTER TABLE ac_protocol ADD CONSTRAINT ac_protocol_FK_3 FOREIGN KEY (proto) REFERENCES ac_protos(id);

ALTER TABLE ac_protocol ADD CONSTRAINT ac_protocol_FK_4 FOREIGN KEY (fixative) REFERENCES ac_fixatives(id);

ALTER TABLE ac_protocol ADD CONSTRAINT ac_protocol_FK_5 FOREIGN KEY (option_) REFERENCES ac_options(id);

ALTER TABLE ac_protocol ADD CONSTRAINT ac_protocol_FK_6 FOREIGN KEY (enzyme) REFERENCES ac_enzymes(id);

ALTER TABLE ac_protocol ADD CONSTRAINT ac_protocol_FK_7 FOREIGN KEY (linker) REFERENCES ac_linkers(id);

ALTER TABLE ac_protocol ADD CONSTRAINT ac_protocol_FK_8 FOREIGN KEY (inc) REFERENCES ac_incs(id);

ALTER TABLE ac_protocol ADD CONSTRAINT ac_protocol_FK_9 FOREIGN KEY (acl_inc) REFERENCES ac_aciincs(id);

ALTER TABLE ac_protocol ADD CONSTRAINT ac_protocol_FK_10 FOREIGN KEY (acll) REFERENCES ac_aciis(id);

ALTER TABLE ac_protocol ADD CONSTRAINT ac_protocol_FK_11 FOREIGN KEY (inc2) REFERENCES ac_incs(id);


-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR bk_*

-- TODO: table bk_access => les valeurs de la colonne `id_access`sont en dur dans le code PHP (BookingaccessibilitiesController l. 33-38)

-- TODO: table bk_authorization => renommer la colonne `resource_id` en `category`ou `category_id` (lien avec re_category, PAS avec re_info)

ALTER TABLE bk_access ADD CONSTRAINT bk_access_FK_1 FOREIGN KEY (id_resource) REFERENCES re_info(id);

ALTER TABLE bk_authorization ADD CONSTRAINT bk_authorization_FK_2 FOREIGN KEY (resource_id) REFERENCES re_category(id);

ALTER TABLE bk_authorization ADD CONSTRAINT bk_authorization_FK_3 FOREIGN KEY (visa_id) REFERENCES re_visas(id);

ALTER TABLE bk_bookingcss ADD CONSTRAINT bk_bookingcss_FK_1 FOREIGN KEY (id_area) REFERENCES re_area(id);

ALTER TABLE bk_calendar_entry ADD CONSTRAINT bk_calendar_entry_FK_1 FOREIGN KEY (resource_id) REFERENCES re_info(id);

ALTER TABLE bk_calendar_entry ADD CONSTRAINT bk_calendar_entry_FK_2 FOREIGN KEY (booked_by_id) REFERENCES core_users(id);

ALTER TABLE bk_calendar_entry ADD CONSTRAINT bk_calendar_entry_FK_3 FOREIGN KEY (recipient_id) REFERENCES core_users(id);

ALTER TABLE bk_calendar_entry ADD CONSTRAINT bk_calendar_entry_FK_4 FOREIGN KEY (responsible_id) REFERENCES cl_clients(id);

ALTER TABLE bk_calendar_entry ADD CONSTRAINT bk_calendar_entry_FK_5 FOREIGN KEY (invoice_id) REFERENCES in_invoice(id);

ALTER TABLE bk_calendar_entry ADD CONSTRAINT bk_calendar_entry_FK_6 FOREIGN KEY (package_id) REFERENCES bk_packages(id);

ALTER TABLE bk_calendar_entry ADD CONSTRAINT bk_calendar_entry_FK_7 FOREIGN KEY (color_type_id) REFERENCES bk_color_codes(id);

ALTER TABLE bk_calendar_entry ADD CONSTRAINT bk_calendar_entry_FK_8 FOREIGN KEY (period_id) REFERENCES bk_calendar_period(id);

ALTER TABLE bk_calquantities ADD CONSTRAINT bk_calquantities_FK_1 FOREIGN KEY (id_resource) REFERENCES re_info(id);

ALTER TABLE bk_calsupinfo ADD CONSTRAINT bk_calsupinfo_FK_1 FOREIGN KEY (id_resource) REFERENCES re_info(id);

ALTER TABLE bk_color_codes ADD CONSTRAINT bk_color_codes_FK_1 FOREIGN KEY (who_can_use) REFERENCES core_user_space_roles(id);

ALTER TABLE bk_nightwe ADD CONSTRAINT bk_nightwe_FK_1 FOREIGN KEY (id_belonging) REFERENCES cl_pricings(id);

ALTER TABLE bk_packages ADD CONSTRAINT bk_packages_FK_1 FOREIGN KEY (id_resource) REFERENCES re_info(id);

ALTER TABLE bk_prices ADD CONSTRAINT bk_prices_FK_1 FOREIGN KEY (id_resource) REFERENCES re_info(id);

ALTER TABLE bk_prices ADD CONSTRAINT bk_prices_FK_2 FOREIGN KEY (id_belonging) REFERENCES cl_pricings(id);

ALTER TABLE bk_restrictions ADD CONSTRAINT bk_restrictions_FK_1 FOREIGN KEY (id_resource) REFERENCES re_info(id);

ALTER TABLE bk_schedulings ADD CONSTRAINT bk_schedulings_FK_1 FOREIGN KEY (default_color_id) REFERENCES bk_color_codes(id);

ALTER TABLE bk_schedulings ADD CONSTRAINT bk_schedulings_FK_2 FOREIGN KEY (id_rearea) REFERENCES re_area(id);


-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR ca_* 

ALTER TABLE ca_entries ADD CONSTRAINT ca_entries_FK_1 FOREIGN KEY (id_category) REFERENCES ca_categories(id);


-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR cache_* 

ALTER TABLE cache_urls_gets ADD CONSTRAINT cache_urls_gets_FK FOREIGN KEY (url_id) REFERENCES cache_urls(id);


-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR cl_* 

ALTER TABLE cl_clients ADD CONSTRAINT cl_clients_FK_1 FOREIGN KEY (pricing) REFERENCES cl_pricings(id);

ALTER TABLE cl_clients ADD CONSTRAINT cl_clients_FK_2 FOREIGN KEY (address_delivery) REFERENCES cl_addresses(id);

ALTER TABLE cl_clients ADD CONSTRAINT cl_clients_FK_3 FOREIGN KEY (address_invoice) REFERENCES cl_addresses(id);


-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR core_* 

ALTER TABLE core_files ADD CONSTRAINT core_files_FK_2 FOREIGN KEY (`role`) REFERENCES core_user_space_roles(id);

ALTER TABLE core_j_spaces_user ADD CONSTRAINT core_j_spaces_user_FK_2 FOREIGN KEY (status) REFERENCES core_user_space_roles(id);

ALTER TABLE core_main_menu_items ADD CONSTRAINT core_main_menu_items_FK_1 FOREIGN KEY (id_sub_menu) REFERENCES core_main_sub_menus(id);

ALTER TABLE core_main_sub_menus ADD CONSTRAINT core_main_sub_menus_FK FOREIGN KEY (id_main_menu) REFERENCES core_main_menus(id);

ALTER TABLE core_pending_accounts ADD CONSTRAINT core_pending_accounts_FK_2 FOREIGN KEY (validated_by) REFERENCES core_users(id);

ALTER TABLE core_space_menus ADD CONSTRAINT core_space_menus_FK_1 FOREIGN KEY (user_role) REFERENCES core_user_space_roles(id);


-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR hp_* 

ALTER TABLE hp_ticket_attachment ADD CONSTRAINT hp_ticket_attachment_FK FOREIGN KEY (id_ticket) REFERENCES hp_tickets(id);

ALTER TABLE hp_ticket_attachment ADD CONSTRAINT hp_ticket_attachment_FK_1 FOREIGN KEY (id_message) REFERENCES hp_ticket_message(id);

ALTER TABLE hp_ticket_attachment ADD CONSTRAINT hp_ticket_attachment_FK_2 FOREIGN KEY (id_file) REFERENCES core_files(id);

ALTER TABLE hp_ticket_message ADD CONSTRAINT hp_ticket_message_FK FOREIGN KEY (id_ticket) REFERENCES hp_tickets(id);

ALTER TABLE hp_tickets ADD CONSTRAINT hp_tickets_FK_2 FOREIGN KEY (assigned) REFERENCES core_users(id);


-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR in_*

ALTER TABLE in_invoice ADD CONSTRAINT in_invoice_FK_3 FOREIGN KEY (id_responsible) REFERENCES cl_clients(id);

ALTER TABLE in_invoice ADD CONSTRAINT in_invoice_FK_4 FOREIGN KEY (visa_send) REFERENCES in_visa(id);

ALTER TABLE in_invoice_item ADD CONSTRAINT in_invoice_item_FK_1 FOREIGN KEY (id_invoice) REFERENCES in_invoice(id);


-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR qo_*

ALTER TABLE qo_quoteitems ADD CONSTRAINT qo_quoteitems_FK_1 FOREIGN KEY (id_quote) REFERENCES qo_quotes(id);

ALTER TABLE qo_quotes ADD CONSTRAINT qo_quotes_FK_2 FOREIGN KEY (id_belonging) REFERENCES cl_pricings(id);

ALTER TABLE qo_quotes ADD CONSTRAINT qo_quotes_FK_3 FOREIGN KEY (id_client) REFERENCES cl_clients(id);


-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR rating*

ALTER TABLE rating ADD CONSTRAINT rating_FK_2 FOREIGN KEY (campaign) REFERENCES rating_campaign(id);

ALTER TABLE rating ADD CONSTRAINT rating_FK_3 FOREIGN KEY (resource) REFERENCES re_info(id);


-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR re_*

ALTER TABLE re_event ADD CONSTRAINT re_event_FK_2 FOREIGN KEY (id_resource) REFERENCES re_info(id);

ALTER TABLE re_event ADD CONSTRAINT re_event_FK_3 FOREIGN KEY (id_eventtype) REFERENCES re_event_type(id);

ALTER TABLE re_event ADD CONSTRAINT re_event_FK_4 FOREIGN KEY (id_state) REFERENCES re_state(id);

ALTER TABLE re_event_data ADD CONSTRAINT re_event_data_FK_1 FOREIGN KEY (id_event) REFERENCES re_event(id);

ALTER TABLE re_info ADD CONSTRAINT re_info_FK_1 FOREIGN KEY (id_category) REFERENCES re_category(id);

ALTER TABLE re_info ADD CONSTRAINT re_info_FK_2 FOREIGN KEY (id_area) REFERENCES re_area(id);

ALTER TABLE re_resps ADD CONSTRAINT re_resps_FK_2 FOREIGN KEY (id_resource) REFERENCES re_info(id);

ALTER TABLE re_resps ADD CONSTRAINT re_resps_FK_3 FOREIGN KEY (id_status) REFERENCES re_resps_status(id);

ALTER TABLE re_visas ADD CONSTRAINT re_visas_FK_2 FOREIGN KEY (id_resource_category) REFERENCES re_category(id);


-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR se_*

-- TODO: revoir le nom de se_project.id_sample_cabinet, actuellement alimentée (et maintenant contrainte) par stock_shelf.id

-- TODO: supprimer la table se_resaassay (pas utilisée dans le code php)

-- TODO: table se_project => les valeurs des colonnes `new_team`et ǹew_project`sont en dur dans le code PHP (ServicesprojectsController l. 456-459)

ALTER TABLE se_order_service ADD CONSTRAINT se_order_service_FK_1 FOREIGN KEY (id_order) REFERENCES se_order(id);

ALTER TABLE se_order_service ADD CONSTRAINT se_order_service_FK_2 FOREIGN KEY (id_service) REFERENCES se_services(id);

ALTER TABLE se_prices ADD CONSTRAINT se_prices_FK_1 FOREIGN KEY (id_service) REFERENCES se_services(id);

ALTER TABLE se_prices ADD CONSTRAINT se_prices_FK_2 FOREIGN KEY (id_belonging) REFERENCES cl_pricings(id);

ALTER TABLE se_project ADD CONSTRAINT se_project_FK_3 FOREIGN KEY (id_origin) REFERENCES se_origin(id);

ALTER TABLE se_project ADD CONSTRAINT se_project_FK_4 FOREIGN KEY (closed_by) REFERENCES se_visa(id);

ALTER TABLE se_project ADD CONSTRAINT se_project_FK_5 FOREIGN KEY (in_charge) REFERENCES se_visa(id);

ALTER TABLE se_project ADD CONSTRAINT se_project_FK_6 FOREIGN KEY (id_sample_cabinet) REFERENCES stock_shelf(id);

ALTER TABLE se_project_service ADD CONSTRAINT se_project_service_FK_1 FOREIGN KEY (id_project) REFERENCES se_project(id);

ALTER TABLE se_project_service ADD CONSTRAINT se_project_service_FK_2 FOREIGN KEY (id_service) REFERENCES se_services(id);

ALTER TABLE se_project_service ADD CONSTRAINT se_project_service_FK_3 FOREIGN KEY (id_invoice) REFERENCES in_invoice(id);

ALTER TABLE se_project_user ADD CONSTRAINT se_project_user_FK_2 FOREIGN KEY (id_project) REFERENCES se_project(id);

ALTER TABLE se_purchase_item ADD CONSTRAINT se_purchase_item_FK_1 FOREIGN KEY (id_purchase) REFERENCES se_purchase(id);

ALTER TABLE se_purchase_item ADD CONSTRAINT se_purchase_item_FK_2 FOREIGN KEY (id_service) REFERENCES se_services(id);

# La table se_service_types n'est plus utilisée par le code php (cf. SeServiceType.php) => on abandonne cette contrainte
# ALTER TABLE se_services ADD CONSTRAINT se_services_FK_1 FOREIGN KEY (type_id) REFERENCES se_service_types(id);

ALTER TABLE se_order ADD CONSTRAINT se_order_FK_2 FOREIGN KEY (id_resp) REFERENCES cl_clients(id);

ALTER TABLE se_order ADD CONSTRAINT se_order_FK_3 FOREIGN KEY (id_invoice) REFERENCES in_invoice(id);

ALTER TABLE se_order ADD CONSTRAINT se_order_FK_4 FOREIGN KEY (created_by_id) REFERENCES core_users(id);

ALTER TABLE se_order ADD CONSTRAINT se_order_FK_5 FOREIGN KEY (modified_by_id) REFERENCES core_users(id);

ALTER TABLE se_task ADD CONSTRAINT se_task_FK_3 FOREIGN KEY (id_project) REFERENCES se_project(id);

ALTER TABLE se_task_category ADD CONSTRAINT se_task_category_FK_1 FOREIGN KEY (id_project) REFERENCES se_project(id);

ALTER TABLE se_task_service ADD CONSTRAINT se_task_service_FK_1 FOREIGN KEY (id_task) REFERENCES se_task(id);

ALTER TABLE se_task_service ADD CONSTRAINT se_task_service_FK_2 FOREIGN KEY (id_service) REFERENCES se_services(id);

-- %%%%%%%%%%%%%%%%%%%%%%%%% CONTRAINTES SUR tables restantes

ALTER TABLE stock_shelf ADD CONSTRAINT stock_shelf_FK_1 FOREIGN KEY (id_cabinet) REFERENCES stock_cabinets(id);

-- À décommenter si nécessaire (cf. en haut)
-- SET sql_mode = (SELECT @@GLOBAL.sql_mode);


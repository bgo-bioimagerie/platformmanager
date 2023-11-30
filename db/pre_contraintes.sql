-- %%%%%%%%%%%%% Avant tout chose

-- on change le type des colonnes 'deleted' à BOOL (au lieu de INT) => MEMOIRE

SET sql_mode = '';

ALTER TABLE platform_manager.ac_aciincs MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_aciis MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_anticorps MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_applications MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_dems MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_enzymes MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_especes MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_fixatives MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_incs MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_isotypes MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_j_tissu_anticorps MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_j_user_anticorps MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_kits MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_linkers MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_options MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_organes MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_prelevements MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_protocol MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_protos MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_sources MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_stainings MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ac_status MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bj_collections MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bj_events MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bj_j_collections_notes MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bj_notes MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bj_tasks MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bj_tasks_history MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_access MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_authorization MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_booking_settings MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_bookingcss MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_calendar_entry MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_calendar_period MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_calquantities MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_calsupinfo MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_calsupplementaries MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_color_codes MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_j_packages_prices MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_nightwe MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_owner_prices MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_packages MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_prices MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_restrictions MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_schedulings MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ca_categories MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ca_entries MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.cache_urls MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.cache_urls_gets MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.cl_addresses MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.cl_clients MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.cl_company MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.cl_j_client_user MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.cl_pricings MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.com_news MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_adminmenu MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_config MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_files MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_history MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_installed_modules MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_j_spaces_user MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_mail_unsubscribe MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_main_menu_items MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_main_menus MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_main_sub_menus MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_openid MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_pending_accounts MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_projects MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_space_access_options MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_space_menus MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_spaces MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_star MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_status MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_user_space_settings MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_users MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_users_settings MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.core_virtual MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.dc_documents MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ec_belongings MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ec_convention MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ec_j_belonging_units MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ec_j_user_responsible MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ec_projects MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ec_units MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.ec_users MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.hp_ticket_attachment MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.hp_ticket_message MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.hp_tickets MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.in_invoice MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.in_invoice_item MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.in_visa MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.mailer_mails MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.pfm_db MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.pfm_upgrade MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.qo_quoteitems MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.qo_quotes MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.re_area MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.re_category MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.re_event MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.re_event_data MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.re_event_type MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.re_info MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.re_resps MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.re_resps_status MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.re_state MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.re_visas MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_order MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_order_service MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_origin MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_prices MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_project MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_project_service MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_project_user MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_purchase MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_purchase_item MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_service_types MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_services MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_task MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_task_category MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_task_service MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.se_visa MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.stats_buckets MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.stock_cabinets MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.stock_shelf MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.users_info MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;

SET sql_mode = (SELECT @@GLOBAL.sql_mode);

-- ajout d'une table manquante pour des jointures sur les rôles (status) attribués aux utilisateurs par espace
CREATE TABLE platform_manager.core_user_space_roles (
	id TINYINT UNSIGNED NOT NULL,
	label varchar(20) NOT NULL,
	CONSTRAINT core_user_space_roles_PK PRIMARY KEY (id)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

INSERT INTO platform_manager.core_user_space_roles (id,label)
	VALUES (0,'inactive');
INSERT INTO platform_manager.core_user_space_roles (id,label)
	VALUES (1,'visitor');
INSERT INTO platform_manager.core_user_space_roles (id,label)
	VALUES (2,'user');
INSERT INTO platform_manager.core_user_space_roles (id,label)
	VALUES (3,'manager');
INSERT INTO platform_manager.core_user_space_roles (id,label)
	VALUES (4,'admin');

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes core_spaces.id

DELETE FROM  ac_j_tissu_anticorps WHERE id_space = 0;

DELETE FROM  ac_j_user_anticorps WHERE id_space = 0;

DELETE FROM bk_access WHERE id_space = 0;

DELETE FROM bk_authorization WHERE id_space = 0;

DELETE FROM bk_bookingcss WHERE id_space = 0;

-- SELECT * FROM bk_calendar_entry bce WHERE id_space =0;
DELETE FROM bk_calendar_entry WHERE id_space = 0;

ALTER TABLE platform_manager.bk_calendar_period 
ENGINE=InnoDB;
DELETE FROM bk_calendar_period WHERE id_space = 0;

DELETE FROM bk_prices WHERE id_space = 0;

ALTER TABLE platform_manager.bk_restrictions 
ENGINE=InnoDB;
DELETE FROM bk_restrictions WHERE id_space = 0;

DELETE FROM bk_schedulings WHERE id_space = 0;

DROP TABLE IF EXISTS platform_manager.br_batchs;

DROP TABLE IF EXISTS platform_manager.br_categories;

DROP TABLE IF EXISTS platform_manager.br_chipping;

DROP TABLE IF EXISTS platform_manager.br_losse_types;

DROP TABLE IF EXISTS platform_manager.br_losses;

DROP TABLE IF EXISTS platform_manager.br_products;

DROP TABLE IF EXISTS platform_manager.br_sale_items;

DROP TABLE IF EXISTS platform_manager.br_sales;

DROP TABLE IF EXISTS platform_manager.br_treatment;

ALTER TABLE platform_manager.ca_categories 
ENGINE=InnoDB;

ALTER TABLE platform_manager.ca_entries 
ENGINE=InnoDB;

ALTER TABLE platform_manager.cache_urls DROP INDEX idx_cache_urls_space;
ALTER TABLE platform_manager.cache_urls DROP COLUMN id_space;

ALTER TABLE platform_manager.cache_urls_gets DROP INDEX idx_cache_urls_gets_space;
ALTER TABLE platform_manager.cache_urls_gets DROP COLUMN id_space;

ALTER TABLE platform_manager.cl_addresses 
ENGINE=InnoDB;
DELETE FROM cl_addresses WHERE id_space = 0;

ALTER TABLE platform_manager.cl_clients 
ENGINE=InnoDB;

ALTER TABLE platform_manager.cl_company 
ENGINE=InnoDB;

ALTER TABLE platform_manager.cl_j_client_user 
ENGINE=InnoDB;
DELETE FROM cl_j_client_user WHERE id_space = 0;

ALTER TABLE platform_manager.cl_pricings 
ENGINE=InnoDB;

ALTER TABLE platform_manager.com_news 
ENGINE=InnoDB;

ALTER TABLE platform_manager.core_adminmenu DROP INDEX idx_core_adminmenu_space;
ALTER TABLE platform_manager.core_adminmenu DROP COLUMN id_space;

DELETE FROM core_config WHERE keyname = 'clientsmenuname' AND id_space = 28;
ALTER TABLE platform_manager.core_config ADD CONSTRAINT core_config_PK PRIMARY KEY (keyname,id_space);
-- TODO: valeurs de conf (à mettre dans un fichier ? cf http://localhost:3000/coreconfigadmin)
DELETE FROM core_config WHERE id_space = 0;

ALTER TABLE platform_manager.core_dashboard_items 
ENGINE=InnoDB;

DROP TABLE IF EXISTS platform_manager.core_dashboard_items;

DROP TABLE IF EXISTS platform_manager.core_dashboard_sections;

DELETE FROM core_history WHERE id_space = 0;

ALTER TABLE platform_manager.core_installed_modules DROP INDEX idx_core_installed_modules_space;
ALTER TABLE platform_manager.core_installed_modules DROP COLUMN id_space;

ALTER TABLE platform_manager.core_main_menu_items 
ENGINE=InnoDB;

ALTER TABLE platform_manager.core_main_menus 
ENGINE=InnoDB;
ALTER TABLE platform_manager.core_main_menus DROP INDEX idx_core_main_menus_space;
ALTER TABLE platform_manager.core_main_menus DROP COLUMN id_space;

ALTER TABLE platform_manager.core_main_sub_menus 
ENGINE=InnoDB;
ALTER TABLE platform_manager.core_main_sub_menus DROP INDEX idx_core_main_sub_menus_space;
ALTER TABLE platform_manager.core_main_sub_menus DROP COLUMN id_space;

ALTER TABLE platform_manager.core_openid DROP INDEX idx_core_openid_space;
ALTER TABLE platform_manager.core_openid DROP COLUMN id_space;

ALTER TABLE platform_manager.core_pending_accounts 
ENGINE=InnoDB;

ALTER TABLE platform_manager.core_space_access_options 
ENGINE=InnoDB;
ALTER TABLE platform_manager.core_space_access_options MODIFY COLUMN id_space int NOT NULL;

ALTER TABLE platform_manager.core_status DROP INDEX idx_core_status_space;
ALTER TABLE platform_manager.core_status DROP COLUMN id_space;

ALTER TABLE platform_manager.core_users MODIFY COLUMN date_created date  NULL;
ALTER TABLE platform_manager.core_users DROP INDEX idx_core_users_space;
ALTER TABLE platform_manager.core_users DROP COLUMN id_space;

ALTER TABLE platform_manager.core_users_settings DROP INDEX idx_core_users_settings_space;
ALTER TABLE platform_manager.core_users_settings DROP COLUMN id_space;

ALTER TABLE platform_manager.core_virtual DROP INDEX idx_core_virtual_space;
ALTER TABLE platform_manager.core_virtual DROP COLUMN id_space;

DROP TABLE platform_manager.db_attributs;

DROP TABLE platform_manager.db_attributs_translate;

DROP TABLE platform_manager.db_classes;

DROP TABLE platform_manager.db_classes_translate;

DROP TABLE platform_manager.db_database;

DROP TABLE platform_manager.db_database_translate;

DROP TABLE platform_manager.db_langs;

DROP TABLE platform_manager.db_menu;

DROP TABLE platform_manager.db_menus_translate;

DROP TABLE platform_manager.db_types;

DROP TABLE platform_manager.db_view_attributs;

DROP TABLE platform_manager.db_views;

DROP TABLE platform_manager.db_views_translate;

SET sql_mode = '';
ALTER TABLE platform_manager.dc_documents MODIFY COLUMN date_modified date NULL;
UPDATE platform_manager.dc_documents
	SET date_modified=NULL
	WHERE date_modified = '0000-00-00';
ALTER TABLE platform_manager.dc_documents 
ENGINE=InnoDB;
SET sql_mode = (SELECT @@GLOBAL.sql_mode);

ALTER TABLE platform_manager.ec_j_belonging_units 
ENGINE=InnoDB;

ALTER TABLE platform_manager.ec_convention 
ENGINE=InnoDB;

ALTER TABLE platform_manager.ec_j_user_responsible DROP INDEX idx_ec_j_user_responsible_space;
ALTER TABLE platform_manager.ec_j_user_responsible DROP COLUMN id_space;

ALTER TABLE platform_manager.ec_units DROP INDEX idx_ec_units_space;
ALTER TABLE platform_manager.ec_units DROP COLUMN id_space;

SET sql_mode = '';
ALTER TABLE platform_manager.ec_users MODIFY COLUMN date_convention date NULL;
UPDATE platform_manager.ec_users 
	SET date_convention =NULL
	WHERE date_convention = '0000-00-00';
ALTER TABLE platform_manager.ec_users 
ENGINE=InnoDB;
SET sql_mode = (SELECT @@GLOBAL.sql_mode);
ALTER TABLE platform_manager.ec_users DROP INDEX idx_ec_users_space;
ALTER TABLE platform_manager.ec_users DROP COLUMN id_space;

DROP TABLE platform_manager.es_cancel_reasons;

DROP TABLE platform_manager.es_contact_types;

DROP TABLE platform_manager.es_delivery_method;

DROP TABLE platform_manager.es_not_feasible_reason;

DROP TABLE platform_manager.es_prices;

DROP TABLE platform_manager.es_product_categories;

DROP TABLE platform_manager.es_product_unit_q;

DROP TABLE platform_manager.es_products;

DROP TABLE platform_manager.es_sale_entered_items;

DROP TABLE platform_manager.es_sale_history;

DROP TABLE platform_manager.es_sale_invoice_items;

DROP TABLE platform_manager.es_sale_items;

DROP TABLE platform_manager.es_sales;

ALTER TABLE platform_manager.hp_ticket_attachment DROP INDEX idx_hp_ticket_attachment_space;
ALTER TABLE platform_manager.hp_ticket_attachment DROP COLUMN id_space;

ALTER TABLE platform_manager.hp_ticket_message DROP INDEX idx_hp_ticket_message_space;
ALTER TABLE platform_manager.hp_ticket_message DROP COLUMN id_space;

-- TODO: valeur de conf (à mettre dans un fichier ?)
DELETE FROM hp_tickets WHERE id_space = 0;

DELETE FROM in_invoice_item WHERE id_space = 0;

ALTER TABLE platform_manager.in_visa 
ENGINE=InnoDB;

ALTER TABLE platform_manager.pfm_db DROP INDEX idx_pfm_db_space;
ALTER TABLE platform_manager.pfm_db DROP COLUMN id_space;

ALTER TABLE platform_manager.pfm_upgrade DROP INDEX idx_pfm_upgrade_space;
ALTER TABLE platform_manager.pfm_upgrade DROP COLUMN id_space;

DELETE FROM qo_quoteitems WHERE id_space = 0;

DELETE FROM re_event WHERE id_space = 0;

DELETE FROM re_resps WHERE id_space = 0;

DELETE FROM re_visas WHERE id_space = 0;

ALTER TABLE platform_manager.se_origin 
ENGINE=InnoDB;

DELETE FROM se_prices WHERE id_space = 0;

DELETE FROM se_project_service WHERE id_space = 0;

ALTER TABLE platform_manager.se_service_types DROP INDEX idx_se_service_types_space;
ALTER TABLE platform_manager.se_service_types DROP COLUMN id_space;

ALTER TABLE platform_manager.se_visa 
ENGINE=InnoDB;

ALTER TABLE platform_manager.stats_buckets DROP INDEX idx_stats_buckets_space;
ALTER TABLE platform_manager.stats_buckets DROP COLUMN id_space;

ALTER TABLE platform_manager.stock_cabinets 
ENGINE=InnoDB;

ALTER TABLE platform_manager.stock_shelf 
ENGINE=InnoDB;
DELETE FROM stock_shelf WHERE id_space = 0;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes core_users.id

-- on rend l'utilisateur "bidon" d'id 1 disponible dans tous les espaces (utile par la suite pour remplacer des références à des users inexistants)
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,1,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,5,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,6,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,8,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,9,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,10,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,12,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,13,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,15,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,17,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,18,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,19,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,21,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,22,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,24,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,25,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,26,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,27,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,28,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,29,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,30,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,31,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,32,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,33,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,34,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,35,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,36,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,37,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,38,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,39,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,40,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,41,4,0);
INSERT INTO platform_manager.core_j_spaces_user (id_user,id_space,status,deleted) VALUES (1,42,4,0);

SET sql_mode = '';
UPDATE core_users 
	SET date_created = NULL 
	WHERE date_created = '0000-00-00';
UPDATE core_users 
	SET date_end_contract = NULL
	WHERE date_end_contract = '0000-00-00';
SET sql_mode = (SELECT @@GLOBAL.sql_mode);

ALTER TABLE platform_manager.core_users MODIFY COLUMN status_id TINYINT UNSIGNED NOT NULL;

ALTER TABLE platform_manager.users_info 
ENGINE=InnoDB;
ALTER TABLE platform_manager.users_info DROP INDEX idx_users_info_space;
ALTER TABLE platform_manager.users_info DROP COLUMN id_space;

DELETE FROM users_info ui WHERE ui.id_core NOT IN (SELECT id FROM core_users);

DELETE FROM core_users_settings cus WHERE cus.user_id NOT IN (SELECT id FROM core_users);

SET sql_mode = '';
UPDATE bk_authorization
	SET date = NULL 
	WHERE date = '0000-00-00';
UPDATE bk_authorization
	SET date_desactivation = NULL 
	WHERE date_desactivation = '0000-00-00';
SET sql_mode = (SELECT @@GLOBAL.sql_mode);

DELETE FROM bk_authorization bka WHERE bka.user_id NOT IN (SELECT id FROM core_users);

DELETE FROM cl_j_client_user WHERE id_user NOT IN (SELECT id FROM core_users);

SET sql_mode = '';
UPDATE core_j_spaces_user 
	SET date_convention = NULL 
	WHERE date_convention = '0000-00-00';
UPDATE core_j_spaces_user 
	SET date_contract_end = NULL 
	WHERE date_contract_end = '0000-00-00';
SET sql_mode = (SELECT @@GLOBAL.sql_mode);

DELETE FROM core_j_spaces_user WHERE id_user NOT IN (SELECT id FROM core_users);

ALTER TABLE platform_manager.core_mail_unsubscribe MODIFY COLUMN id_user INT NOT NULL;

DELETE FROM core_star WHERE id_user NOT IN (SELECT id FROM core_users);

DELETE FROM ec_j_user_responsible WHERE id_user NOT IN (SELECT id FROM core_users);

DELETE FROM ec_j_user_responsible WHERE id_resp NOT IN (SELECT id FROM cl_clients);

DELETE FROM ec_users WHERE id NOT IN (SELECT id FROM core_users);

UPDATE hp_tickets 
	SET created_by_user = 1
	WHERE created_by_user = 0;

-- TODO: hp_tickets : supprimer les colonnes 'created_by' et 'assigned_name'

UPDATE in_invoice 
	SET id_responsible = 1
	WHERE id_responsible = 0;

UPDATE in_invoice 
	SET id_edited_by = 1
	WHERE id_edited_by = 0;

UPDATE in_invoice 
	SET id_edited_by = 1
	WHERE id_edited_by = -1;

SET sql_mode = '';
ALTER TABLE in_invoice MODIFY COLUMN date_paid date NULL;
UPDATE in_invoice 
	SET date_paid = NULL 
	WHERE date_paid = '0000-00-00';
UPDATE in_invoice 
	SET period_begin = NULL 
	WHERE period_begin = '0000-00-00';
UPDATE in_invoice 
	SET period_end = NULL 
	WHERE period_end = '0000-00-00';
SET sql_mode = (SELECT @@GLOBAL.sql_mode);

SET sql_mode = '';
ALTER TABLE qo_quotes MODIFY COLUMN date_open date NULL;
UPDATE qo_quotes  
	SET date_open = NULL 
	WHERE date_open = '0000-00-00';
SET sql_mode = (SELECT @@GLOBAL.sql_mode);

UPDATE qo_quotes 
	SET id_user = NULL
	WHERE id_user = 0;

UPDATE re_visas 
	SET id_instructor = 1
	WHERE id_instructor = 0;

UPDATE se_project 
	SET id_user = 1
	WHERE id_user = 0;

SET sql_mode = '';
ALTER TABLE se_project MODIFY COLUMN samplereturndate date NULL;
UPDATE se_project 
	SET samplereturndate = NULL 
	WHERE samplereturndate = '0000-00-00';
SET sql_mode = (SELECT @@GLOBAL.sql_mode);

UPDATE se_project_user
	SET id_user = 1
	WHERE id_user = 0;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes cl_clients.id

-- on insert des comptes clients 'bidons' pour pourvoir poser des contraintes de clés (notamment bk_calendar_entry#responsible_id -> cl_clients#id)
INSERT INTO platform_manager.cl_clients (id,id_space,name,contact_name)
	VALUES (8,8,'-- --','-- --');
INSERT INTO platform_manager.cl_clients (id_space,name,contact_name)
	VALUES (40,'-- --','-- --');
INSERT INTO platform_manager.cl_clients (id_space,name,contact_name)
	VALUES (25,'-- --','-- --');

-- DELETE FROM se_project sp WHERE sp.id_resp NOT IN (SELECT id FROM cl_clients); => Non ! Trop brutal, préférer SET id_resp = 1 (voire NULL ?)
UPDATE se_project sp
	-- SET sp.id_resp = 1 -- NON PLUS !! => ne prend pas en compte le core_spaces#id (incohérence entre se_project#id_space et cl_clients#id_space)
	SET sp.id_resp = (SELECT cc.id FROM cl_clients cc WHERE cc.id_space = sp.id_space AND (cc.name = '' OR cc.name = '-- --') LIMIT 1)
	WHERE sp.id_resp NOT IN (SELECT id FROM cl_clients);

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes ac_* (module anticorps)

ALTER TABLE platform_manager.ac_anticorps MODIFY COLUMN id_staining INT DEFAULT 1 NOT NULL;
ALTER TABLE platform_manager.ac_anticorps MODIFY COLUMN id_application INT DEFAULT 1 NOT NULL;

UPDATE ac_anticorps AS ac SET ac.id_application = 1 WHERE ac.id_application = 0;

UPDATE ac_anticorps AS ac SET ac.id_staining = 1 WHERE ac.id_staining = 0;

SET sql_mode = '';
ALTER TABLE platform_manager.ac_j_user_anticorps MODIFY COLUMN date_recept date NULL;
UPDATE platform_manager.ac_j_user_anticorps  
	SET date_recept = NULL
	WHERE date_recept = '0000-00-00';
SET sql_mode = (SELECT @@GLOBAL.sql_mode);

UPDATE ac_j_user_anticorps 
	SET date_recept = REPLACE (date_recept, '-00', '-01')
	WHERE date_recept LIKE '%-00%';

DELETE FROM ac_j_user_anticorps WHERE id_anticorps = 0;

DELETE FROM ac_j_user_anticorps WHERE id_utilisateur = 0;

INSERT INTO ac_dems VALUES (0, '--', 8, 0, NULL, CURRENT_TIMESTAMP(), CURRENT_TIMESTAMP());
UPDATE ac_dems SET id = 0 WHERE nom = '--';

UPDATE ac_protocol SET kit = 8 WHERE kit = 0;

UPDATE ac_protocol SET proto = 1 WHERE proto = 0;

UPDATE ac_protocol SET inc = 1 WHERE inc = 0;

UPDATE ac_protocol SET acl_inc = 1 WHERE acl_inc = 0;

UPDATE ac_protocol SET acll = 3 WHERE acll = 0;

UPDATE ac_protocol SET inc2 = 1 WHERE inc2 = 0;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes bk_*

-- TODO: WARN bk_authorization#resource_id renvoie bien à re_category#id (PAS à re_info#id)
DELETE FROM bk_authorization ba WHERE ba.resource_id NOT IN (SELECT id FROM re_category);

-- En dessous : peut-être excessif ? (1459 lignes effacées de bk_authorization) => à discuter 
DELETE FROM bk_authorization ba WHERE ba.visa_id NOT IN (SELECT id FROM re_visas);

-- cf BkAuthorization#getDistinctUnitForPeriod (deprecated => BkAuthorization.php l. 160-168)
ALTER TABLE bk_authorization DROP COLUMN lab_id;

-- cf BkCalendarEntry.php l. 266
UPDATE bk_calendar_entry
	SET booked_by_id = 1
	WHERE booked_by_id NOT IN (SELECT id FROM core_users);

UPDATE bk_calendar_entry
	SET recipient_id = 1
	WHERE recipient_id NOT IN (SELECT id FROM core_users);

UPDATE bk_calendar_entry bce
LEFT OUTER JOIN cl_j_client_user cjcu ON cjcu.id_user = bce.recipient_id
INNER JOIN cl_clients cc ON cc.id = cjcu.id_client  AND cc.id_space = bce.id_space
	SET bce.responsible_id = cc.id
WHERE bce.responsible_id NOT IN (SELECT cc.id FROM cl_clients cc);

DELETE FROM bk_calendar_entry WHERE responsible_id = 0 AND id_space = 6; 

UPDATE bk_calendar_entry bce
	SET bce.responsible_id = (SELECT cc.id FROM cl_clients cc WHERE cc.id_space = bce.id_space AND (cc.name = '' OR cc.name = '-- --') LIMIT 1)
WHERE bce.responsible_id NOT IN (SELECT cc.id FROM cl_clients cc);

ALTER TABLE platform_manager.bk_calendar_entry MODIFY COLUMN invoice_id int NULL;

UPDATE bk_calendar_entry bce
	SET bce.invoice_id = NULL
	WHERE bce.invoice_id NOT IN (SELECT id FROM in_invoice);

ALTER TABLE platform_manager.bk_calendar_entry MODIFY COLUMN package_id int NULL;

UPDATE bk_calendar_entry bce
	SET bce.package_id = NULL
	WHERE bce.package_id NOT IN (SELECT id FROM bk_packages);

ALTER TABLE platform_manager.bk_calendar_entry MODIFY COLUMN period_id int NULL;

UPDATE bk_calendar_entry bce
	SET bce.period_id = NULL 
	WHERE bce.period_id NOT IN (SELECT id FROM bk_calendar_period);

ALTER TABLE platform_manager.bk_color_codes MODIFY COLUMN who_can_use TINYINT UNSIGNED DEFAULT 1 NOT NULL;

DELETE FROM bk_nightwe bn WHERE bn.id_belonging NOT IN (SELECT id FROM cl_pricings);

-- TODO : bk_packages#id_package, bk_prices#id_package renvoient à core_virtual#id (cf. BookingsupsabstractController.php l. 129...)

ALTER TABLE platform_manager.bk_prices MODIFY COLUMN id_belonging int NULL;

UPDATE bk_prices bp
	SET bp.id_belonging = NULL
	WHERE bp.id_belonging = 0;

ALTER TABLE platform_manager.bk_schedulings MODIFY COLUMN is_monday BOOL DEFAULT 1 NOT NULL;
ALTER TABLE platform_manager.bk_schedulings MODIFY COLUMN is_tuesday BOOL DEFAULT 1 NOT NULL;
ALTER TABLE platform_manager.bk_schedulings MODIFY COLUMN is_wednesday BOOL DEFAULT 1 NOT NULL;
ALTER TABLE platform_manager.bk_schedulings MODIFY COLUMN is_thursday BOOL DEFAULT 1 NOT NULL;
ALTER TABLE platform_manager.bk_schedulings MODIFY COLUMN is_friday BOOL DEFAULT 1 NOT NULL;
ALTER TABLE platform_manager.bk_schedulings MODIFY COLUMN is_saturday BOOL DEFAULT 1 NOT NULL;
ALTER TABLE platform_manager.bk_schedulings MODIFY COLUMN is_sunday BOOL DEFAULT 1 NOT NULL;
ALTER TABLE platform_manager.bk_schedulings MODIFY COLUMN shared BOOL DEFAULT 0 NOT NULL;
ALTER TABLE platform_manager.bk_schedulings MODIFY COLUMN force_packages BOOL DEFAULT 0 NOT NULL;

DELETE FROM bk_schedulings bs WHERE bs.id_rearea NOT IN (SELECT id FROM re_area);

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes ca_*DELETE FROM bk_schedulings bs WHERE bs.id_rearea NOT IN (SELECT id FROM re_area)

DELETE FROM ca_entries ce WHERE ce.id_category NOT IN (SELECT id FROM ca_categories);

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes cache_*

ALTER TABLE platform_manager.cache_urls MODIFY COLUMN isapi BOOL DEFAULT 0 NOT NULL;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes cl_*

UPDATE cl_clients cc
	SET cc.pricing = NULL
	WHERE cc.pricing = 0;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes core_*

ALTER TABLE platform_manager.core_files MODIFY COLUMN `role` TINYINT UNSIGNED NOT NULL;

-- TODO: envisager la création d'une table `core_files_status` pour contraindre core_files#status (et remplacer CoreFiles.php l. 12..15)

ALTER TABLE platform_manager.core_j_spaces_user MODIFY COLUMN status TINYINT UNSIGNED NOT NULL;

UPDATE platform_manager.core_main_sub_menus
	SET id_main_menu = 13
	WHERE id_main_menu = 2;

DELETE FROM core_main_sub_menus cmsm WHERE cmsm.id_main_menu NOT IN (SELECT id FROM core_main_menus);

ALTER TABLE platform_manager.core_pending_accounts MODIFY COLUMN validated BOOL NULL;

UPDATE core_pending_accounts cpa
	SET cpa.validated_by = NULL 
	WHERE cpa.validated_by NOT IN (SELECT id FROM core_users);

ALTER TABLE platform_manager.core_space_menus MODIFY COLUMN user_role TINYINT UNSIGNED DEFAULT 1 NOT NULL;

ALTER TABLE platform_manager.core_space_menus MODIFY COLUMN has_sub_menu BOOL DEFAULT 1 NOT NULL;

ALTER TABLE platform_manager.core_status MODIFY COLUMN id TINYINT UNSIGNED auto_increment NOT NULL;

ALTER TABLE platform_manager.core_users MODIFY COLUMN is_active BOOL DEFAULT 1 NOT NULL;

ALTER TABLE platform_manager.core_users MODIFY COLUMN validated BOOL DEFAULT 1 NOT NULL;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes hp_*

ALTER TABLE platform_manager.hp_tickets MODIFY COLUMN unread BOOL DEFAULT 0 NULL;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes in_*

ALTER TABLE platform_manager.in_invoice MODIFY COLUMN id_unit int NULL;

UPDATE in_invoice iv
	SET iv.id_unit = NULL
	WHERE id_unit = 0;

UPDATE in_invoice ii
	SET ii.id_responsible = (SELECT cc.id FROM cl_clients cc WHERE cc.id_space = ii.id_space AND (cc.name = '' OR cc.name = '-- --') LIMIT 1)
WHERE ii.id_responsible NOT IN (SELECT cc.id FROM cl_clients cc);

-- TODO: virer la colonne in_invoice#id_project (pas utilisée, que des 0...);

ALTER TABLE platform_manager.in_invoice MODIFY COLUMN is_paid BOOL DEFAULT 1 NOT NULL;

ALTER TABLE platform_manager.in_invoice MODIFY COLUMN visa_send int NULL;

UPDATE in_invoice ii
	SET ii.visa_send = NULL
	WHERE ii.visa_send = 0;

DELETE FROM in_invoice_item it WHERE it.id_invoice NOT IN (SELECT id FROM in_invoice);

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes qo_*

-- TODO: qo_quoteitems#id_content renvoie à re_info#id si qo_quoteitems#module == 'booking' et à se_services#id si qo_quoteitems#module == 'services'

UPDATE qo_quotes qq 
	SET qq.id_belonging = NULL
	WHERE qq.id_belonging = 0;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes re_*

ALTER TABLE platform_manager.re_area MODIFY COLUMN restricted BOOL DEFAULT 0 NOT NULL;

DELETE FROM re_event re WHERE re.id_eventtype NOT IN (SELECT id FROM re_event_type);

DELETE FROM re_event_data red WHERE red.id_event NOT IN (SELECT id FROM re_event);

UPDATE re_info ri
	SET ri.id_category = 250
	WHERE ri.id_space = 4;

DELETE FROM bk_calendar_entry bce
	WHERE bce.resource_id IN (SELECT id FROM re_info ri WHERE ri.id_category NOT IN (SELECT id FROM re_category));

DELETE FROM bk_access ba WHERE ba.id_resource IN (SELECT id FROM re_info ri WHERE ri.id_category NOT IN (SELECT id FROM re_category));

SELECT * FROM bk_access ba WHERE ba.id_resource NOT IN (SELECT id FROM re_info);

DELETE FROM bk_restrictions br WHERE br.id_resource IN (SELECT id FROM re_info ri WHERE ri.id_category NOT IN (SELECT id FROM re_category));

DELETE FROM bk_prices bp WHERE bp.id_resource IN (SELECT id FROM re_info ri WHERE ri.id_category NOT IN (SELECT id FROM re_category));

DELETE FROM re_info ri WHERE ri.id_category NOT IN (SELECT id FROM re_category);

DELETE FROM re_resps rr WHERE rr.id_resource NOT IN (SELECT id FROM re_info);

ALTER TABLE platform_manager.re_visas MODIFY COLUMN is_active BOOL DEFAULT 1 NOT NULL;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes se_* (module presta services)

-- choix un peu arbitraire mais à priori sur de vieilles données...
UPDATE se_project sp
	SET sp.id_origin = (SELECT id FROM se_origin so WHERE sp.id_space = so.id_space LIMIT 1)
	WHERE sp.id_origin NOT IN (SELECT id FROM se_origin);

ALTER TABLE platform_manager.se_project MODIFY COLUMN closed_by INT NULL;

UPDATE se_project
	SET closed_by = NULL 
	WHERE closed_by = 0;

UPDATE se_project 
	SET closed_by = NULL 
	WHERE closed_by NOT IN (SELECT id FROM se_visa);

UPDATE se_project
	SET in_charge = 1
	WHERE in_charge = 0 OR in_charge NOT IN (SELECT id FROM se_visa);

ALTER TABLE platform_manager.se_project MODIFY COLUMN id_sample_cabinet INT NULL;

UPDATE se_project 
	SET id_sample_cabinet = NULL
	WHERE id_sample_cabinet NOT IN (SELECT id FROM stock_shelf);

DELETE FROM se_project_service sps WHERE sps.id_project NOT IN (SELECT id FROM se_project);

UPDATE se_project_service sps
	SET sps.`date` = (SELECT date_open FROM se_project sp WHERE sp.id=sps.id_project)
	WHERE sps.`date` = 0;

ALTER TABLE platform_manager.se_project_service MODIFY COLUMN id_invoice INT NULL;

UPDATE se_project_service
	SET id_invoice = NULL
	WHERE id_invoice NOT IN (SELECT id FROM in_invoice);

DROP TABLE platform_manager.se_resaassay;

ALTER TABLE platform_manager.se_order MODIFY COLUMN id_invoice INT NULL;

ALTER TABLE platform_manager.se_order MODIFY COLUMN created_by_id INT NULL;

ALTER TABLE platform_manager.se_order MODIFY COLUMN modified_by_id INT NULL;

ALTER TABLE platform_manager.se_services MODIFY COLUMN is_active BOOL DEFAULT 1 NOT NULL;

ALTER TABLE platform_manager.se_task MODIFY COLUMN done BOOL DEFAULT 0 NOT NULL;

ALTER TABLE platform_manager.se_task MODIFY COLUMN private BOOL DEFAULT 0 NOT NULL;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes tables restantes







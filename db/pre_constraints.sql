-- %%%%%%%%%%%%% On change le type des colonnes 'deleted' à BOOL (au lieu de INT) => MEMOIRE

SET sql_mode = '';

ALTER TABLE ac_aciincs MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_aciis MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_anticorps MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_applications MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_dems MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_enzymes MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_especes MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_fixatives MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_incs MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_isotypes MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_j_tissu_anticorps MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_j_user_anticorps MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_kits MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_linkers MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_options MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_organes MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_prelevements MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_protocol MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_protos MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_sources MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_stainings MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ac_status MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_access MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_authorization MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_booking_settings MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_bookingcss MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_calendar_entry MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_calendar_period MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_calquantities MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_calsupinfo MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_color_codes MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_j_packages_prices MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_nightwe MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_owner_prices MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_packages MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_prices MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_restrictions MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_schedulings MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ca_categories MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE ca_entries MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE cache_urls MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE cache_urls_gets MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE cl_addresses MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE cl_clients MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE cl_company MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE cl_j_client_user MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE cl_pricings MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE com_news MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_adminmenu MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_config MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_files MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_history MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_installed_modules MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_j_spaces_user MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_mail_unsubscribe MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_main_menu_items MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_main_menus MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_main_sub_menus MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_openid MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_pending_accounts MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_projects MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_space_access_options MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_space_menus MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_spaces MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_star MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_user_space_settings MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_users MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_users_settings MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE core_virtual MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE dc_documents MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE hp_ticket_attachment MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE hp_ticket_message MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE hp_tickets MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE in_invoice MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE in_invoice_item MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE in_visa MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE mailer_mails MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE pfm_db MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE pfm_upgrade MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE qo_quoteitems MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE qo_quotes MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE re_area MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE re_category MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE re_event MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE re_event_data MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE re_event_type MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE re_info MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE re_resps MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE re_resps_status MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE re_state MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE re_visas MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_order MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_order_service MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_origin MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_prices MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_project MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_project_service MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_project_user MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_purchase MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_purchase_item MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_service_types MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_services MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_task MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_task_category MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_task_service MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE se_visa MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE stats_buckets MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE stock_cabinets MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE stock_shelf MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;
ALTER TABLE users_info MODIFY COLUMN deleted BOOL DEFAULT 0 NOT NULL;

SET sql_mode = (SELECT @@GLOBAL.sql_mode);

--  %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Ajout d'une table manquante pour des jointures sur les rôles (status) attribués aux utilisateurs par espace
DROP TABLE IF EXISTS core_user_space_roles;

CREATE TABLE core_user_space_roles (
	id TINYINT UNSIGNED NOT NULL,
	label varchar(20) NOT NULL,
	CONSTRAINT core_user_space_roles_PK PRIMARY KEY (id)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;

INSERT INTO core_user_space_roles  (id,label)
	VALUES (0,'inactive')
	     , (1,'visitor')
	     , (2,'user')
	     , (3,'manager')
	     , (4,'admin')
    ON DUPLICATE KEY UPDATE label = label;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes core_spaces.id

ALTER TABLE bk_calendar_period ENGINE=InnoDB;

ALTER TABLE bk_restrictions ENGINE=InnoDB;

DROP TABLE IF EXISTS br_batchs;

DROP TABLE IF EXISTS br_categories;

DROP TABLE IF EXISTS br_chipping;

DROP TABLE IF EXISTS br_losse_types;

DROP TABLE IF EXISTS br_losses;

DROP TABLE IF EXISTS br_products;

DROP TABLE IF EXISTS br_sale_items;

DROP TABLE IF EXISTS br_sales;

DROP TABLE IF EXISTS br_treatment;

ALTER TABLE ca_categories ENGINE=InnoDB;

ALTER TABLE ca_entries ENGINE=InnoDB;

ALTER TABLE cache_urls DROP INDEX idx_cache_urls_space;
ALTER TABLE cache_urls DROP COLUMN id_space;

ALTER TABLE cache_urls_gets DROP INDEX idx_cache_urls_gets_space;
ALTER TABLE cache_urls_gets DROP COLUMN id_space;

ALTER TABLE cl_addresses ENGINE=InnoDB;

ALTER TABLE cl_clients ENGINE=InnoDB;

ALTER TABLE cl_company ENGINE=InnoDB;

ALTER TABLE cl_j_client_user ENGINE=InnoDB;

ALTER TABLE cl_pricings ENGINE=InnoDB;

ALTER TABLE com_news ENGINE=InnoDB;

ALTER TABLE core_adminmenu DROP INDEX idx_core_adminmenu_space;
ALTER TABLE core_adminmenu DROP COLUMN id_space;

DROP TABLE IF EXISTS core_dashboard_items;

DROP TABLE IF EXISTS core_dashboard_sections;

ALTER TABLE core_installed_modules DROP INDEX idx_core_installed_modules_space;
ALTER TABLE core_installed_modules DROP COLUMN id_space;

ALTER TABLE core_main_menu_items ENGINE=InnoDB;

ALTER TABLE core_main_menus ENGINE=InnoDB;
ALTER TABLE core_main_menus DROP INDEX idx_core_main_menus_space;
ALTER TABLE core_main_menus DROP COLUMN id_space;

ALTER TABLE core_main_sub_menus ENGINE=InnoDB;
ALTER TABLE core_main_sub_menus DROP INDEX idx_core_main_sub_menus_space;
ALTER TABLE core_main_sub_menus DROP COLUMN id_space;

ALTER TABLE core_openid DROP INDEX idx_core_openid_space;
ALTER TABLE core_openid DROP COLUMN id_space;

ALTER TABLE core_pending_accounts ENGINE=InnoDB;

ALTER TABLE core_space_access_options ENGINE=InnoDB;
ALTER TABLE core_space_access_options MODIFY COLUMN id_space int NOT NULL;

ALTER TABLE core_users MODIFY COLUMN date_created date  NULL;
ALTER TABLE core_users DROP INDEX idx_core_users_space;
ALTER TABLE core_users DROP COLUMN id_space;

ALTER TABLE core_users_settings DROP INDEX idx_core_users_settings_space;
ALTER TABLE core_users_settings DROP COLUMN id_space;

ALTER TABLE core_virtual DROP INDEX idx_core_virtual_space;
ALTER TABLE core_virtual DROP COLUMN id_space;

DROP TABLE IF EXISTS db_attributs;

DROP TABLE IF EXISTS  db_attributs_translate;

DROP TABLE IF EXISTS  db_classes;

DROP TABLE IF EXISTS  db_classes_translate;

DROP TABLE IF EXISTS  db_database;

DROP TABLE IF EXISTS  db_database_translate;

DROP TABLE IF EXISTS  db_langs;

DROP TABLE IF EXISTS  db_menu;

DROP TABLE IF EXISTS  db_menus_translate;

DROP TABLE IF EXISTS  db_types;

DROP TABLE IF EXISTS  db_view_attributs;

DROP TABLE IF EXISTS  db_views;

DROP TABLE IF EXISTS  db_views_translate;

SET sql_mode = '';
ALTER TABLE dc_documents MODIFY COLUMN date_modified date NULL;
UPDATE dc_documents
	SET date_modified=NULL
	WHERE date_modified = '0000-00-00';
ALTER TABLE dc_documents ENGINE=InnoDB;
SET sql_mode = (SELECT @@GLOBAL.sql_mode);


DROP TABLE IF EXISTS  es_cancel_reasons;

DROP TABLE IF EXISTS  es_contact_types;

DROP TABLE IF EXISTS  es_delivery_method;

DROP TABLE IF EXISTS  es_not_feasible_reason;

DROP TABLE IF EXISTS  es_prices;

DROP TABLE IF EXISTS  es_product_categories;

DROP TABLE IF EXISTS  es_product_unit_q;

DROP TABLE IF EXISTS  es_products;

DROP TABLE IF EXISTS  es_sale_entered_items;

DROP TABLE IF EXISTS  es_sale_history;

DROP TABLE IF EXISTS  es_sale_invoice_items;

DROP TABLE IF EXISTS  es_sale_items;

DROP TABLE IF EXISTS  es_sales;

ALTER TABLE hp_ticket_attachment DROP INDEX idx_hp_ticket_attachment_space;
ALTER TABLE hp_ticket_attachment DROP COLUMN id_space;

ALTER TABLE hp_ticket_message DROP INDEX idx_hp_ticket_message_space;
ALTER TABLE hp_ticket_message DROP COLUMN id_space;

ALTER TABLE in_visa ENGINE=InnoDB;

ALTER TABLE pfm_db DROP INDEX idx_pfm_db_space;
ALTER TABLE pfm_db DROP COLUMN id_space;

ALTER TABLE pfm_upgrade DROP INDEX idx_pfm_upgrade_space;
ALTER TABLE pfm_upgrade DROP COLUMN id_space;

ALTER TABLE se_origin ENGINE=InnoDB;

ALTER TABLE se_service_types DROP INDEX idx_se_service_types_space;
ALTER TABLE se_service_types DROP COLUMN id_space;

ALTER TABLE se_visa ENGINE=InnoDB;

ALTER TABLE stats_buckets DROP INDEX idx_stats_buckets_space;
ALTER TABLE stats_buckets DROP COLUMN id_space;

ALTER TABLE stock_cabinets ENGINE=InnoDB;

ALTER TABLE stock_shelf ENGINE=InnoDB;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes core_users.id

SET sql_mode = '';
UPDATE core_users 
	SET date_created = NULL 
	WHERE date_created = '0000-00-00';
UPDATE core_users 
	SET date_end_contract = NULL
	WHERE date_end_contract = '0000-00-00';
SET sql_mode = (SELECT @@GLOBAL.sql_mode);

ALTER TABLE core_users MODIFY COLUMN status_id TINYINT UNSIGNED NOT NULL;

ALTER TABLE users_info 
ENGINE=InnoDB;
ALTER TABLE users_info DROP INDEX idx_users_info_space;
ALTER TABLE users_info DROP COLUMN id_space;

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

DELETE FROM bk_authorization WHERE user_id NOT IN (SELECT id FROM core_users);

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

ALTER TABLE core_mail_unsubscribe MODIFY COLUMN id_user INT NOT NULL;

DELETE FROM core_star WHERE id_user NOT IN (SELECT id FROM core_users);

-- TODO: hp_tickets : supprimer les colonnes 'created_by' et 'assigned_name'

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

SET sql_mode = '';
ALTER TABLE se_project MODIFY COLUMN samplereturndate date NULL;
UPDATE se_project 
	SET samplereturndate = NULL 
	WHERE samplereturndate = '0000-00-00';
SET sql_mode = (SELECT @@GLOBAL.sql_mode);

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes cl_clients.id

-- on insert des comptes clients 'bidons' pour pourvoir poser des contraintes de clés (notamment bk_calendar_entry#responsible_id -> cl_clients#id)
INSERT INTO cl_clients(id_space,name,contact_name)
SELECT DISTINCT id_space,'-- --','-- --' FROM cl_clients
WHERE id_space NOT IN (
    SELECT DISTINCT id_space FROM cl_clients cc WHERE cc.name = '' OR cc.name = '-- --');

-- DELETE FROM se_project sp WHERE sp.id_resp NOT IN (SELECT id FROM cl_clients); => Non ! Trop brutal, préférer SET id_resp = 1 (voire NULL ?)
UPDATE se_project sp
	-- SET sp.id_resp = 1 -- NON PLUS !! => ne prend pas en compte le core_spaces#id (incohérence entre se_project#id_space et cl_clients#id_space)
	SET sp.id_resp = (SELECT cc.id FROM cl_clients cc WHERE cc.id_space = sp.id_space AND (cc.name = '' OR cc.name = '-- --') LIMIT 1)
	WHERE sp.id_resp NOT IN (SELECT id FROM cl_clients);

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes ac_* (module anticorps)

ALTER TABLE ac_anticorps MODIFY COLUMN id_staining INT DEFAULT 1 NOT NULL;
ALTER TABLE ac_anticorps MODIFY COLUMN id_application INT DEFAULT 1 NOT NULL;

UPDATE ac_anticorps AS ac SET ac.id_application = 1 WHERE ac.id_application = 0;

UPDATE ac_anticorps AS ac SET ac.id_staining = 1 WHERE ac.id_staining = 0;

SET sql_mode = '';
ALTER TABLE ac_j_user_anticorps MODIFY COLUMN date_recept date NULL;
UPDATE ac_j_user_anticorps  
	SET date_recept = NULL
	WHERE date_recept = '0000-00-00';
SET sql_mode = (SELECT @@GLOBAL.sql_mode);

UPDATE ac_j_user_anticorps 
	SET date_recept = REPLACE (date_recept, '-00', '-01')
	WHERE date_recept LIKE '%-00%';


-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes bk_*

-- TODO: WARN bk_authorization#resource_id renvoie bien à re_category#id (PAS à re_info#id)
DELETE FROM bk_authorization WHERE resource_id NOT IN (SELECT id FROM re_category);

-- pas ici car on efface des re_visas plus bas => voir section re_*
# DELETE FROM bk_authorization WHERE visa_id NOT IN (SELECT id FROM re_visas);

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

SET sql_mode = '';
UPDATE bk_calendar_entry bc
INNER JOIN (
        SELECT bce.id, cc.id AS cl_id FROM bk_calendar_entry bce
        INNER JOIN cl_clients cc ON bce.id_space = cc.id_space AND (cc.name = '' OR cc.name = '-- --')
        WHERE bce.responsible_id NOT IN (SELECT cl.id FROM cl_clients cl)
        GROUP BY bce.id) bc_
ON bc.id = bc_.id
SET bc.responsible_id = bc_.cl_id;
SET sql_mode = (SELECT @@GLOBAL.sql_mode);

ALTER TABLE bk_calendar_entry MODIFY COLUMN invoice_id int NULL;

UPDATE bk_calendar_entry bce
	SET bce.invoice_id = NULL
	WHERE bce.invoice_id NOT IN (SELECT id FROM in_invoice);

ALTER TABLE bk_calendar_entry MODIFY COLUMN package_id int NULL;

UPDATE bk_calendar_entry bce
	SET bce.package_id = NULL
	WHERE bce.package_id NOT IN (SELECT id FROM bk_packages);

ALTER TABLE bk_calendar_entry MODIFY COLUMN period_id int NULL;

UPDATE bk_calendar_entry bce
	SET bce.period_id = NULL 
	WHERE bce.period_id NOT IN (SELECT id FROM bk_calendar_period);

ALTER TABLE bk_color_codes MODIFY COLUMN who_can_use TINYINT UNSIGNED DEFAULT 1 NOT NULL;

DELETE FROM bk_nightwe bn WHERE bn.id_belonging NOT IN (SELECT id FROM cl_pricings);

-- TODO : bk_packages#id_package, bk_prices#id_package renvoient à core_virtual#id (cf. BookingsupsabstractController.php l. 129...)

ALTER TABLE bk_prices MODIFY COLUMN id_belonging int NULL;

ALTER TABLE bk_schedulings MODIFY COLUMN is_monday BOOL DEFAULT 1 NOT NULL;
ALTER TABLE bk_schedulings MODIFY COLUMN is_tuesday BOOL DEFAULT 1 NOT NULL;
ALTER TABLE bk_schedulings MODIFY COLUMN is_wednesday BOOL DEFAULT 1 NOT NULL;
ALTER TABLE bk_schedulings MODIFY COLUMN is_thursday BOOL DEFAULT 1 NOT NULL;
ALTER TABLE bk_schedulings MODIFY COLUMN is_friday BOOL DEFAULT 1 NOT NULL;
ALTER TABLE bk_schedulings MODIFY COLUMN is_saturday BOOL DEFAULT 1 NOT NULL;
ALTER TABLE bk_schedulings MODIFY COLUMN is_sunday BOOL DEFAULT 1 NOT NULL;
ALTER TABLE bk_schedulings MODIFY COLUMN shared BOOL DEFAULT 0 NOT NULL;
ALTER TABLE bk_schedulings MODIFY COLUMN force_packages BOOL DEFAULT 0 NOT NULL;

DELETE FROM bk_schedulings bs WHERE bs.id_rearea NOT IN (SELECT id FROM re_area);

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes ca_*

DELETE FROM ca_entries ce WHERE ce.id_category NOT IN (SELECT id FROM ca_categories);

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes cache_*

ALTER TABLE cache_urls MODIFY COLUMN isapi BOOL DEFAULT 0 NOT NULL;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes cl_*

ALTER TABLE cl_clients MODIFY COLUMN `address_delivery` INT(11) NULL;

ALTER TABLE cl_clients MODIFY COLUMN `address_invoice` INT(11) NULL;

ALTER TABLE cl_clients MODIFY COLUMN `pricing` INT(11) NULL;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes core_*

ALTER TABLE core_files MODIFY COLUMN `role` TINYINT UNSIGNED NOT NULL;

-- TODO: envisager la création d'une table `core_files_status` pour contraindre core_files#status (et remplacer CoreFiles.php l. 12..15)

ALTER TABLE core_j_spaces_user MODIFY COLUMN status TINYINT UNSIGNED NOT NULL;

DELETE FROM core_main_sub_menus cmsm WHERE cmsm.id_main_menu NOT IN (SELECT id FROM core_main_menus);

ALTER TABLE core_pending_accounts MODIFY COLUMN validated BOOL DEFAULT FALSE NOT NULL;

ALTER TABLE core_pending_accounts MODIFY COLUMN validated_by INT NULL;

UPDATE core_pending_accounts cpa
	SET cpa.validated_by = NULL 
	WHERE cpa.validated_by NOT IN (SELECT id FROM core_users);

ALTER TABLE core_space_menus MODIFY COLUMN user_role TINYINT UNSIGNED DEFAULT 1 NOT NULL;

ALTER TABLE core_space_menus MODIFY COLUMN has_sub_menu BOOL DEFAULT 1 NOT NULL;

ALTER TABLE core_users MODIFY COLUMN is_active BOOL DEFAULT 1 NOT NULL;

ALTER TABLE core_users MODIFY COLUMN validated BOOL DEFAULT 1 NOT NULL;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes hp_*

ALTER TABLE hp_tickets MODIFY COLUMN unread BOOL DEFAULT 0 NULL;

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes in_*

ALTER TABLE in_invoice MODIFY COLUMN id_unit int NULL;

UPDATE in_invoice ii
	SET ii.id_responsible = (SELECT cc.id FROM cl_clients cc WHERE cc.id_space = ii.id_space AND (cc.name = '' OR cc.name = '-- --') LIMIT 1)
WHERE ii.id_responsible NOT IN (SELECT cc.id FROM cl_clients cc);

-- TODO: virer la colonne in_invoice#id_project (pas utilisée, que des 0...);

ALTER TABLE in_invoice MODIFY COLUMN is_paid BOOL DEFAULT 1 NOT NULL;

ALTER TABLE in_invoice MODIFY COLUMN visa_send int NULL;

DELETE FROM in_invoice_item it WHERE it.id_invoice NOT IN (SELECT id FROM in_invoice);

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes qo_*

-- TODO: qo_quoteitems#id_content renvoie à re_info#id si qo_quoteitems#module == 'booking' et à se_services#id si qo_quoteitems#module == 'services'

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes re_*

ALTER TABLE re_area MODIFY COLUMN restricted BOOL DEFAULT 0 NOT NULL;

DELETE FROM re_event re WHERE re.id_eventtype NOT IN (SELECT id FROM re_event_type);

DELETE FROM re_event_data red WHERE red.id_event NOT IN (SELECT id FROM re_event);

DELETE FROM bk_calendar_entry bce
	WHERE bce.resource_id IN (SELECT id FROM re_info ri WHERE ri.id_category NOT IN (SELECT id FROM re_category));

DELETE FROM bk_access ba WHERE ba.id_resource IN (SELECT id FROM re_info ri WHERE ri.id_category NOT IN (SELECT id FROM re_category));

DELETE FROM bk_restrictions br WHERE br.id_resource IN (SELECT id FROM re_info ri WHERE ri.id_category NOT IN (SELECT id FROM re_category));

DELETE FROM bk_prices bp WHERE bp.id_resource IN (SELECT id FROM re_info ri WHERE ri.id_category NOT IN (SELECT id FROM re_category));

DELETE FROM re_info ri WHERE ri.id_category NOT IN (SELECT id FROM re_category);

DELETE FROM re_resps rr WHERE rr.id_resource NOT IN (SELECT id FROM re_info);

ALTER TABLE re_visas MODIFY COLUMN is_active BOOL DEFAULT 1 NOT NULL;

DELETE FROM re_visas WHERE id_instructor NOT IN (SELECT id FROM core_users);

DELETE FROM re_visas WHERE id_resource_category NOT IN (SELECT id FROM re_category);

-- on joue l'effacement de bk_authorization ici car dépend de re_visas (et on en efface juste au-dessus)
DELETE FROM bk_authorization WHERE visa_id NOT IN (SELECT id FROM re_visas);

-- %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%% Avant mise en place contraintes se_* (module presta services)

-- choix un peu arbitraire mais à priori sur de vieilles données...
UPDATE se_project sp
	SET sp.id_origin = (SELECT id FROM se_origin so WHERE sp.id_space = so.id_space LIMIT 1)
	WHERE sp.id_origin NOT IN (SELECT id FROM se_origin);

ALTER TABLE se_project MODIFY COLUMN closed_by INT NULL;

UPDATE se_project
	SET closed_by = NULL 
	WHERE closed_by NOT IN (SELECT id FROM se_visa);

ALTER TABLE se_project MODIFY COLUMN id_sample_cabinet INT NULL;

UPDATE se_project 
	SET id_sample_cabinet = NULL
	WHERE id_sample_cabinet NOT IN (SELECT id FROM stock_shelf);

DELETE FROM se_project_service sps WHERE sps.id_project NOT IN (SELECT id FROM se_project);

ALTER TABLE se_project_service MODIFY COLUMN id_invoice INT NULL;

UPDATE se_project_service
	SET id_invoice = NULL
	WHERE id_invoice NOT IN (SELECT id FROM in_invoice);

DROP TABLE IF EXISTS  se_resaassay;

ALTER TABLE se_order MODIFY COLUMN id_invoice INT NULL;

ALTER TABLE se_order MODIFY COLUMN created_by_id INT NULL;

ALTER TABLE se_order MODIFY COLUMN modified_by_id INT NULL;

ALTER TABLE se_services MODIFY COLUMN is_active BOOL DEFAULT 1 NOT NULL;

INSERT INTO se_service_types (id, name, local_name, deleted, deleted_at, created_at, updated_at)
VALUES (1, 'Quantity', 'Quantité', 0, NULL,NOW(), NOW())
     , (2, 'Time minutes', 'Temps en minutes', 0, NULL, NOW(), NOW())
     , (3, 'Time hours', 'Temps en heures', 0, NULL, NOW(), NOW())
     , (4, 'Price', 'Prix', 0, NULL, NOW(), NOW())
     , (5, 'Half day', 'Demi journée', 0, NULL, NOW(), NOW())
     , (6, 'Journée', 'Journée', 0, NULL, NOW(), NOW())
AS new
ON DUPLICATE KEY UPDATE   name = new.name
                        , local_name = new.local_name
                        , deleted = new.deleted
                        , deleted_at = new.deleted_at
                        , updated_at = new.updated_at;

ALTER TABLE se_task MODIFY COLUMN done BOOL DEFAULT 0 NOT NULL;

ALTER TABLE se_task MODIFY COLUMN private BOOL DEFAULT 0 NOT NULL;

UPDATE stock_shelf
    SET id_cabinet = NULL
    WHERE id_cabinet NOT IN (SELECT id FROM stock_cabinets);

<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';

require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/booking/Model/BkAuthorization.php';
require_once 'Modules/booking/Model/BkNightWE.php';
require_once 'Modules/booking/Model/BkRestrictions.php';
require_once 'Modules/booking/Model/BkScheduling.php';
require_once 'Modules/bulletjournal/Model/BjCollection.php';
require_once 'Modules/bulletjournal/Model/BjCollectionNote.php';
require_once 'Modules/bulletjournal/Model/BjEvent.php';
require_once 'Modules/bulletjournal/Model/BjNote.php';
require_once 'Modules/bulletjournal/Model/BjTask.php';
require_once 'Modules/bulletjournal/Model/BjTaskHistory.php';
require_once 'Modules/clients/Model/ClAddress.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClClientUser.php';
require_once 'Modules/clients/Model/ClCompany.php';
require_once 'Modules/clients/Model/ClPricing.php';
require_once 'Modules/com/Model/ComNews.php';
require_once 'Modules/core/Model/CoreAdminMenu.php';
require_once 'Modules/core/Model/CoreMainMenu.php';
require_once 'Modules/core/Model/CoreMainMenuItem.php';
require_once 'Modules/core/Model/CoreMainSubMenu.php';
require_once 'Modules/core/Model/CorePendingAccount.php';
require_once 'Modules/core/Model/CoreSpaceAccessOptions.php';
require_once 'Modules/core/Model/CoreSpaceUser.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/documents/Model/Document.php';
require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';
require_once 'Modules/quote/Model/Quote.php';
require_once 'Modules/quote/Model/QuoteItem.php';
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ReEvent.php';
require_once 'Modules/resources/Model/ReEventData.php';
require_once 'Modules/resources/Model/ReResps.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ReState.php';
require_once 'Modules/services/Model/SePurchase.php';
require_once 'Modules/services/Model/SePurchaseItem.php';
require_once 'Modules/services/Model/StockCabinet.php';
require_once 'Modules/services/Model/StockShelf.php';
require_once 'Modules/users/Model/UsersInfo.php';

# Upgrade: fix db creation
class CoreUpgradeDB1655982779 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply fix db creation");
    $m = new CoreDB();
    $m->base();
    $m = new BkAccess();
    $m->addColumn($m->tableName, "id_resource", "int(11)", 0);
    $m->addColumn($m->tableName, "id_access", "int(11)", 0);
    $m = new BkAuthorization();
    $this->addColumn($m->tableName, "user_id", "int(11)", 0);
    $this->addColumn($m->tableName, "resource_id", "int(11)", 0);
    $this->addColumn($m->tableName, "visa_id", "int(11)", 0);
    $this->addColumn($m->tableName, "date", "date", "");
    $this->addColumn($m->tableName, "date_desactivation", "date", "");
    $this->addColumn($m->tableName, "is_active", "int(1)", 1);
    $m= new BkNightWE();
    $this->addColumn($m->tableName, "id_belonging", "int(11)", 0);
    $this->addColumn($m->tableName, "tarif_unique", "int(11)", 1);
    $this->addColumn($m->tableName, "tarif_night", "int(3)", 0);
    $this->addColumn($m->tableName, "night_start", "int(3)", 19);
    $this->addColumn($m->tableName, "night_end", "int(11)", 8);
    $this->addColumn($m->tableName, "tarif_we", "int(3)", 0);
    $this->addColumn($m->tableName, "choice_we", "varchar(100)", "");
    $m = new BkRestrictions();
    $this->addColumn($m->tableName, "id_resource", "int(11)", 0);
    $this->addColumn($m->tableName, "maxbookingperday", "int(11)", 0);
    $this->addColumn($m->tableName, "bookingdelayusercanedit", "int(11)", 0);
    $m = new BkScheduling();
    $this->addColumn($m->tableName, "is_monday", "int(1)", 1);
    $this->addColumn($m->tableName, "is_tuesday", "int(1)", 1);
    $this->addColumn($m->tableName, "is_wednesday", "int(1)", 1);
    $this->addColumn($m->tableName, "is_thursday", "int(1)", 1);
    $this->addColumn($m->tableName, "is_friday", "int(1)", 1);
    $this->addColumn($m->tableName, "is_saturday", "int(1)", 1);
    $this->addColumn($m->tableName, "is_sunday", "int(1)", 1);
    $this->addColumn($m->tableName, "day_begin", "int(2)", 8);
    $this->addColumn($m->tableName, "day_end", "int(2)", 18);
    $this->addColumn($m->tableName, "size_bloc_resa", "int(4)", 3600);
    $this->addColumn($m->tableName, "booking_time_scale", "int(5)", 1);
    $this->addColumn($m->tableName, "resa_time_setting", "int(1)", 1);
    $this->addColumn($m->tableName, "default_color_id", "int(11)", 1);
    $this->addColumn($m->tableName, "id_rearea", "int(11)", 0);
    $this->addColumn($m->tableName, "force_packages", "tinyint", 0);
    $this->addColumn($m->tableName, 'shared', 'tinyint', 0);
    $m = new BjCollection();
    $this->addColumn($m->tableName, "id_space", "int(11)", 0);
    $this->addColumn($m->tableName, "name", "varchar(250)", "");
    $m = new BjCollectionNote();
    $this->addColumn($m->tableName, "id_collection", "int(11)", 0);
    $this->addColumn($m->tableName, "id_note", "int(11)", 0);
    $m = new BjEvent();
    $this->addColumn($m->tableName, "id_note", "int(11)", 0);
    $this->addColumn($m->tableName, "start_time", "int(11)", 0);
    $this->addColumn($m->tableName, "end_time", "int(11)", 0);
    $m= new BjNote();
    $this->addColumn($m->tableName, "name", "varchar(250)", "");
    $this->addColumn($m->tableName, "type", "int(11)", 0);
    $this->addColumn($m->tableName, "content", "text", "");
    $this->addColumn($m->tableName, "date", "date", "");
    $this->addColumn($m->tableName, "is_month_task", "int(1)", "0");
    $m = new BjTask();
    $this->addColumn($m->tableName, "id_note", "int(11)", 0);
    $this->addColumn($m->tableName, "priority", "int(5)", 0);
    $this->addColumn($m->tableName, "deadline", "date", "");
    $m = new BjTaskHistory();
    $this->addColumn($m->tableName, "id_note", "int(11)", 0);
    $this->addColumn($m->tableName, "status", "int(5)", 1);
    $this->addColumn($m->tableName, "date", "int(11)", 0);
    $m = new ClAddress();
    $this->addColumn($m->tableName, "institution", "varchar(255)", "");
    $this->addColumn($m->tableName, "building_floor", "varchar(255)", "");
    $this->addColumn($m->tableName, "service", "varchar(255)", "");
    $this->addColumn($m->tableName, "address", "text", "");
    $this->addColumn($m->tableName, "zip_code", "varchar(20)", "");
    $this->addColumn($m->tableName, "city", "varchar(255)", "");
    $this->addColumn($m->tableName, "country", "varchar(255)", "");
    $m = new ClClient();
    $this->addColumn($m->tableName, "name", "varchar(255)", "");
    $this->addColumn($m->tableName, "contact_name", "varchar(255)", "");
    $this->addColumn($m->tableName, "address_delivery", "int(11)", 0);
    $this->addColumn($m->tableName, "address_invoice", "int(11)", 0);
    $this->addColumn($m->tableName, "phone", "varchar(20)", "");
    $this->addColumn($m->tableName, "email", "varchar(255)", "");
    $this->addColumn($m->tableName, "pricing", "int(11)", "");
    $this->addColumn($m->tableName, "invoice_send_preference", "int(11)", 0);
    $m = new ClClientUser();
    $this->addColumn($m->tableName, "id_client", "int(11)", 0);
    $this->addColumn($m->tableName, "id_user", "int(11)", 0);
    $m = new ClCompany();
    $this->addColumn($m->tableName, "name", "varchar(255)", 0);
    $this->addColumn($m->tableName, "address", "text", ""); 
    $this->addColumn($m->tableName, "zipcode", "varchar(255)", 0);
    $this->addColumn($m->tableName, "city", "varchar(255)", 0);
    $this->addColumn($m->tableName, "county", "varchar(255)", 0);
    $this->addColumn($m->tableName, "country", "varchar(255)", 0);
    $this->addColumn($m->tableName, "tel", "varchar(255)", 0);
    $this->addColumn($m->tableName, "fax", "varchar(255)", 0);
    $this->addColumn($m->tableName, "email", "varchar(255)", 0);
    $this->addColumn($m->tableName, "approval_number", "varchar(255)", 0);
    $m = new ClPricing();
    $this->addColumn($m->tableName, "name", "varchar(255)", "");
    $this->addColumn($m->tableName, "color", "varchar(7)", "");
    $this->addColumn($m->tableName, "txtcolor", "varchar(7)", "");
    $this->addColumn($m->tableName, "type", "int(1)", 0);
    $this->addColumn($m->tableName, "display_order", "int(11)", 0);
    $m = new ComNews();
    $this->addColumn($m->tableName, "title", "varchar(250)", "");
    $this->addColumn($m->tableName, "content", "TEXT", "");
    $this->addColumn($m->tableName, "media", "TEXT", "");
    $this->addColumn($m->tableName, "date", "date", "");
    $this->addColumn($m->tableName, "expires", "date", "");
    $m = new CoreAdminMenu();
    $this->addColumn($m->tableName, "name", "varchar(40)", "");
    $this->addColumn($m->tableName, "link", "varchar(150)", "");
    $this->addColumn($m->tableName, "icon", "varchar(40)", "");
    $this->addColumn($m->tableName, "display_order", "int(11)", 0);
    $m = new CoreMainMenu();
    $this->addColumn($m->tableName, "name", "varchar(100)", "");
    $this->addColumn($m->tableName, "display_order", "int(11)", 0);
    $m = new CoreMainMenuItem();
    $this->addColumn($m->tableName, "name", "varchar(100)", "");
    $this->addColumn($m->tableName, "id_sub_menu", "int(11)", 0);
    $this->addColumn($m->tableName, "id_space", "int(11)", 0);
    $this->addColumn($m->tableName, "display_order", "int(11)", 0);
    $m = new CoreMainSubMenu();
    $this->addColumn($m->tableName, "name", "varchar(100)", "");
    $this->addColumn($m->tableName, "id_main_menu", "int(11)", 0);
    $this->addColumn($m->tableName, "display_order", "int(11)", 0);
    $m = new CorePendingAccount();
    $this->addColumn($m->tableName, "id_user", "int(11)", 0);
    $this->addColumn($m->tableName, "validated", "int(1)", 0);
    $this->addColumn($m->tableName, "date", "date", "");
    $this->addColumn($m->tableName, "validated_by", "int(11)", 0);
    $m = new CoreSpaceAccessOptions();
    $this->addColumn($m->tableName, "toolname", "varchar(100)", "");
    $this->addColumn($m->tableName, "module", "varchar(100)", "");
    $this->addColumn($m->tableName, "url", "varchar(255)", "");
    $m = new CoreSpaceUser();
    $this->addColumn($m->tableName, "id_user", "int(11)", "");
    $this->addColumn($m->tableName, "id_space", "int(11)", "");
    $this->addColumn($m->tableName, "status", "varchar(100)", "");
    $this->addColumn($m->tableName, "date_convention", "date", "");
    $this->addColumn($m->tableName, "convention_url", "varchar(255)", "");
    $this->addColumn($m->tableName, "date_contract_end", "date", "");
    $m = new CoreUser();
    $this->addColumn($m->tableName, "login", "varchar(100)", "");
    $this->addColumn($m->tableName, "pwd", "varchar(100)", "");
    $this->addColumn($m->tableName, "hash", "int", "0");
    $this->addColumn($m->tableName, "name", "varchar(100)", "");
    $this->addColumn($m->tableName, "firstname", "varchar(100)", "");
    $this->addColumn($m->tableName, "email", "varchar(255)", "");
    $this->addColumn($m->tableName, "phone", "varchar(255)", "");
    $this->addColumn($m->tableName, "status_id", "int(2)", 1);
    $this->addColumn($m->tableName, "source", "varchar(30)", "local");
    $this->addColumn($m->tableName, "is_active", "int(1)", 1);
    $this->addColumn($m->tableName, "date_created", "date", "");
    $this->addColumn($m->tableName, "date_end_contract", "date", "");
    $this->addColumn($m->tableName, "date_last_login", "date", "");
    $this->addColumn($m->tableName, "remember_key", "varchar(255)", "");
    $this->addColumn($m->tableName, "validated", "int(1)", 1);
    $this->addColumn($m->tableName, "apikey", "varchar(30)", "");
    $m = new Document();
    $this->addColumn($m->tableName, "title", "varchar(250)", "");
    $this->addColumn($m->tableName, "id_user", "int(11)", 0);
    $this->addColumn($m->tableName, "date_modified", "date", "");
    $this->addColumn($m->tableName, "url", "TEXT", "");
    $this->addColumn($m->tableName, 'visibility', 'int', 0);
    $this->addColumn($m->tableName, 'id_ref', 'int', '');
    $m = new InInvoice();
    $this->addColumn($m->tableName, "number", "varchar(50)", "");
    $this->addColumn($m->tableName, "period_begin", "date", "");
    $this->addColumn($m->tableName, "period_end", "date", "");
    $this->addColumn($m->tableName, "date_generated", "date", "");
    $this->addColumn($m->tableName, "date_send", "date", "");
    $this->addColumn($m->tableName, "visa_send", "int(11)", 0);
    $this->addColumn($m->tableName, "date_paid", "date", "");
    $this->addColumn($m->tableName, "id_unit", "int(11)", 0);
    $this->addColumn($m->tableName, "id_responsible", "int(11)", 0);
    $this->addColumn($m->tableName, "total_ht", "varchar(50)", "0");
    $this->addColumn($m->tableName, "id_project", "int(11)", 0);
    $this->addColumn($m->tableName, 'title', 'varchar(255)', "");
    $this->addColumn($m->tableName, "is_paid", "int(1)", 0);
    $this->addColumn($m->tableName, "module", "varchar(200)", "");
    $this->addColumn($m->tableName, "controller", "varchar(200)", "");
    $this->addColumn($m->tableName, "id_edited_by", "int(11)", 0);
    $this->addColumn($m->tableName, "discount", "varchar(100)", 0);
    $m = new InInvoiceItem();
    $this->addColumn($m->tableName, "id_invoice", "int(11)", 0);
    $this->addColumn($m->tableName, "module", "varchar(200)", 0);
    $this->addColumn($m->tableName, "controller", "varchar(200)", 0);
    $this->addColumn($m->tableName, "content", "text", "");
    $this->addColumn($m->tableName, "details", "text", "");
    $this->addColumn($m->tableName, "total_ht", "varchar(50)", "0");
    $m = new Quote();
    $this->addColumn($m->tableName, "recipient", "varchar(100)", "");
    $this->addColumn($m->tableName, "recipient_email", "varchar(100)", "");
    $this->addColumn($m->tableName, "address", "text", "");
    $this->addColumn($m->tableName, "id_belonging", "int(11)", "");
    $this->addColumn($m->tableName, "id_user", "int(11)", "");
    $this->addColumn($m->tableName, "id_client", "int(11)", "");
    $this->addColumn($m->tableName, "date_open", "date", "");
    $this->addColumn($m->tableName, "date_last_modified", "date", "");
    $m = new QuoteItem();
    $this->addColumn($m->tableName, "id_quote", "int(11)", 0);
    $this->addColumn($m->tableName, "id_content", "int(11)", 0);
    $this->addColumn($m->tableName, "module", "varchar(255)", "");
    $this->addColumn($m->tableName, "quantity", "varchar(255)", "");
    $this->addColumn($m->tableName, "comment", "TEXT", "");
    $m = new ReArea();
    $this->addColumn($m->tableName, "name", "varchar(250)", "");
    $this->addColumn($m->tableName, "id_space", "int(11)", 0);
    $this->addColumn($m->tableName, "restricted", "int(1)", 0);
    $m = new ReEvent();
    $this->addColumn($m->tableName, "id_resource", "int(11)", 0);
    $this->addColumn($m->tableName, "date", "date", "");
    $this->addColumn($m->tableName, "id_user", "int(11)", 0);
    $this->addColumn($m->tableName, "id_eventtype", "int(11)", 0);
    $this->addColumn($m->tableName, "id_state", "int(11)", 0);
    $this->addColumn($m->tableName, "comment", "text", "");
    $m = new ReEventData();
    $this->addColumn($m->tableName, "id_event", "int(11)", "");
    $this->addColumn($m->tableName, "url", "varchar(255)", "");
    $m = new ReResps();
    $this->addColumn($m->tableName, "id_resource", "int(11)", 0);
    $this->addColumn($m->tableName, "id_user", "int(11)", 0);
    $this->addColumn($m->tableName, "id_status", "int(11)", 0);
    $m = new ResourceInfo();
    $this->addColumn($m->tableName, "name", "varchar(150)", "");
    $this->addColumn($m->tableName, "brand", "varchar(250)", "");
    $this->addColumn($m->tableName, "type", "varchar(250)", "");
    $this->addColumn($m->tableName, "description", "varchar(500)", "");
    $this->addColumn($m->tableName, "long_description", "text", "");
    $this->addColumn($m->tableName, "id_category", "int(11)", 0);
    $this->addColumn($m->tableName, "id_area", "int(11)", 0);
    $this->addColumn($m->tableName, "id_space", "int(11)", 0);
    $this->addColumn($m->tableName, "display_order", "int(11)", 0);
    $this->addColumn($m->tableName, "image", "varchar(255)", "");
    $m = new ReState();
    $this->addColumn($m->tableName, "name", "varchar(250)", "");
    $this->addColumn($m->tableName, "color", "varchar(7)", Constants::COLOR_WHITE);
    $m = new SePurchase();
    $this->addColumn($m->tableName, "comment", "varchar(255)", "");
    $this->addColumn($m->tableName, "id_space", "int(11)", 0);
    $this->addColumn($m->tableName, "date", "date", "");
    $this->addColumn($m->tableName, "doc_url", "varchar(250)", "");
    $m = new SePurchaseItem();
    $this->addColumn($m->tableName, "id_purchase", "int(11)", 0);
    $this->addColumn($m->tableName, "id_service", "int(11)", 0);
    $this->addColumn($m->tableName, "quantity", "varchar(100)", "0");
    $this->addColumn($m->tableName, "comment", "varchar(255)", "");
    $m = new StockCabinet();
    $this->addColumn($m->tableName, "name", "varchar(255)", "");
    $this->addColumn($m->tableName, "room_number", "varchar(255)", "");
    $m = new StockShelf();
    $this->addColumn($m->tableName, "name", "varchar(255)", "");
    $this->addColumn($m->tableName, "id_cabinet", "int(11)", 0);
    $m = new UsersInfo();
    $this->addColumn($m->tableName, "id_core", "int(11)", 0);
    $this->addColumn($m->tableName, "phone", "varchar(100)", "");
    $this->addColumn($m->tableName, "unit", "varchar(255)", "");
    $this->addColumn($m->tableName, "organization", "varchar(255)", "");
    $this->addColumn($m->tableName, "avatar", "varchar(255)", "");
    $this->addColumn($m->tableName, "bio", "text", "");


    // delete package with zero id
    $sql = "DELETE FROM bk_j_packages_prices WHERE id_package IN(SELECT id FROM bk_packages WHERE id_package=0)";
    $this->runRequest($sql);

    $sql = "DELETE FROM bk_packages WHERE id_package = 0";
    $this->runRequest($sql);

    // add columns if no exists
    $sql = "SHOW COLUMNS FROM `ca_entries` LIKE 'image_url'";
    $pdo = $this->runRequest($sql);
    $isColumn = $pdo->fetch();
    if ($isColumn === false) {
        $sql = "ALTER TABLE `ca_entries` ADD `image_url` varchar(300) NOT NULL";
        $this->runRequest($sql);
    }

    $sqlCol = "SHOW COLUMNS FROM `core_config` WHERE Field='id';";
    $reqCol = $this->runRequest($sqlCol);

    if ($reqCol->rowCount() > 0){
        $sql = "ALTER TABLE core_config CHANGE id `keyname` varchar(30) NOT NULL DEFAULT '';";
        $this->runRequest($sql);
        $sql = "ALTER TABLE core_config drop primary key;";
        $this->runRequest($sql);
    }

    $sql = "DELETE FROM core_status WHERE id > 5";
    $this->runRequest($sql);


    $this->addColumn("cache_urls", "isapi", "int(1)", 0);

    $this->addColumn("ac_j_user_anticorps", "id_space", "int(11)", 0);

    $this->addColumn("ac_anticorps", "id_staining", "float(11)", 1);
    $this->addColumn("ac_anticorps", "id_application", "float(11)", 1);
    $this->addColumn("ac_anticorps", "export_catalog", "int(1)", 0);
    $this->addColumn("ac_anticorps", "image_url", "varchar(250)", "");
    $this->addColumn("ac_anticorps", "image_desc", "varchar(250)", "");
    $this->addColumn("ac_anticorps", "id_space", "int(11)", 0);

    $this->addColumn("ac_status", "display_order", "int(11)", 0);

    $this->addColumn("ac_j_tissu_anticorps", "image_url", "varchar(512)", "");

    $this->addColumn('bk_calendar_entry', 'period_id', 'int(11)', 0);
    $this->addColumn('bk_calendar_entry', 'all_day_long', 'int(1)', 0);

    $this->addColumn('bk_calendar_period', 'enddate', 'date', "");

    $this->addColumn("bk_color_codes", "who_can_use", "int(11)", 1);

    $this->addColumn("bk_packages", "id_package", "int(11)", 0);

    $this->addColumn("ca_categories", "display_order", "int(4)", 0);
    $this->addColumn("ca_categories", "id_space", "int(11)", 0);

    $this->addColumn("ca_entries", "id_space", "int(11)", 0);

    $this->addColumn('core_config', 'id_space', 'int(11)', 0);

    $this->addColumn('core_spaces', 'color', 'varchar(7)', "#000000");
    $this->addColumn('core_spaces', 'description', 'text', '');
    $this->addColumn('core_spaces', 'image', "varchar(255)", '');
    $this->addColumn('core_spaces', 'txtcolor', 'varchar(7)', "#ffffff");
    $this->addColumn('core_spaces', 'plan', "int", '0');
    $this->addColumn('core_spaces', 'plan_expire', "int", '0');
    $this->addColumn('core_spaces', 'user_desactivate', "int(1)", '1');
    $this->addColumn('core_spaces', 'termsofuse', "varchar(255)", '');
    $this->addColumn('core_spaces', 'on_user_desactivate', "int", '0');

    $this->addColumn('core_space_menus', 'display_order', 'int(11)', 0);
    $this->addColumn('core_space_menus', 'has_sub_menu', "int(1)", 1);
    $this->addColumn('core_space_menus', 'color', "varchar(7)", "#000000");
    $this->addColumn('core_space_menus', 'txtcolor', "varchar(7)", "#ffffff");

    $this->addColumn('re_visas', 'is_active', 'int(1)', 1);

    $this->addColumn("se_order", "id_resp", "int(11)", 0);
    $this->addColumn("se_order", "id_invoice", "int(11)", 0);
    $this->addColumn("se_order", "created_by_id", "int(11)", 0);
    $this->addColumn("se_order", "modified_by_id", "int(11)", 0);

    $this->addColumn('se_origin', 'display_order', 'int(11)', 0);

    $this->addColumn('se_project', 'id_origin', 'int(11)', 0);
    $this->addColumn('se_project', 'closed_by', 'int(11)', 0);
    $this->addColumn('se_project', 'in_charge', 'int(11)', 0);
    $this->addColumn('se_project', 'samplereturn', 'TEXT', '');
    $this->addColumn('se_project', 'samplereturndate', 'date', '');
    $this->addColumn('se_project', 'id_sample_cabinet', 'int(11)', 0);
    $this->addColumn('se_project', 'samplestocked', 'int(1)', 0);
    $this->addColumn('se_project', 'samplescomment', 'TEXT', "");
    Configuration::getLogger()->info("[db][upgrade] Apply fix db creation, done!");
  }
}
$db = new CoreUpgradeDB1655982779();
$db->run();
?>

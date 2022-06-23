<?php
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
# Upgrade: fix db creation
class CoreUpgradeDB1655982779 extends Model {
  public function run(){
    Configuration::getLogger()->info("[db][upgrade] Apply fix db creation");

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

  }
}
$db = new CoreUpgradeDB1655982779();
$db->run();
?>

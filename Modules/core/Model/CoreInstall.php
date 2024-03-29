<?php

require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/FCache.php';
require_once 'Framework/Errors.php';
require_once 'Framework/Statistics.php';
require_once 'Framework/Events.php';
require_once 'Framework/Constants.php';


require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreFiles.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Modules/core/Model/CoreAdminMenu.php';
require_once 'Modules/core/Model/CoreUserSettings.php';
require_once 'Modules/core/Model/CoreUserSpaceSettings.php';

require_once 'Modules/core/Model/CoreProjects.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreInstalledModules.php';
require_once 'Modules/core/Model/CoreMainMenu.php';
require_once 'Modules/core/Model/CoreMainSubMenu.php';
require_once 'Modules/core/Model/CoreMainMenuItem.php';
require_once 'Modules/core/Model/CoreMainMenuPatch.php';
require_once 'Modules/core/Model/CorePendingAccount.php';
require_once 'Modules/core/Model/CoreSpaceUser.php';
require_once 'Modules/core/Model/CoreSpaceAccessOptions.php';
require_once 'Modules/core/Model/CoreOpenId.php';
require_once 'Modules/core/Model/CoreAdminMenu.php';
require_once 'Modules/core/Model/CoreVirtual.php';
require_once 'Modules/core/Model/CoreStar.php';
require_once 'Modules/users/Model/UsersPatch.php';
require_once 'Modules/users/Model/UsersInfo.php';
require_once 'Modules/core/Model/CoreHistory.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/core/Model/CoreMail.php';


define("DB_VERSION", 5);
/**
 * Class defining the database version installed
 */
class CoreDB extends Model
{
    /**
     * Drops all tables content
     *
     * @param bool $drop delete tables and not just content
     */
    public function dropAll($drop=false)
    {
        $sql = "SHOW tables";
        $tables = $this->runRequest($sql)->fetchAll();
        foreach ($tables as $tb) {
            $table = $tb[0];
            if ($drop) {
                Configuration::getLogger()->warning('Drop table', ["table" => $table]);
                $sql = "DROP TABLE ".$table;
                $this->runRequest($sql);
            } else {
                Configuration::getLogger()->warning('Delete table content', ["table" => $table]);
                $sql = "DELETE FROM ".$table;
                $this->runRequest($sql);
            }
        }
    }

    public function isFreshInstall()
    {
        $sql = "SHOW tables";
        $nbTables = $this->runRequest($sql)->rowCount();
        $freshInstall = true;
        if ($nbTables > 0) {
            $freshInstall = false;
        }
        return $freshInstall;
    }

    /**
     * For tests only
     */
    public function repair0()
    {
        Configuration::getLogger()->info("No bug 0, nothing to repair");
    }

    /**
     * Fix for bug #332 introduced by release 2.1
     * if you installed 2.1->2.1.2 this patch needs to be used to fix database
     * Not needed after 2.1.3
     *
     * How-to:
     *
     * require_once 'Framework/Configuration.php';
     * require_once 'Modules/core/Model/CoreInstall.php';
     * $cdb = new CoreDB();
     * $cdb->repair332();
     *
     */
    public function repair332()
    {
        Configuration::getLogger()->info("Run repair script for bug 332");
        $sql = "SELECT * FROM `re_category`;";
        $resdb = $this->runRequest($sql)->fetchAll();
        foreach ($resdb as $res) {
            $sql = "UPDATE bk_authorization SET id_space=? WHERE resource_id=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }
        Configuration::getLogger()->info("Done!");
    }

    public function repair337()
    {
        Configuration::getLogger()->debug('set ac_anticorps counters');
        $sql = "SELECT max(no_h2p2) as counter, id_space FROM ac_anticorps GROUP BY id_space";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            $redis = new Redis();
            $redis->pconnect(Configuration::get('redis_host', 'redis'), Configuration::get('redis_port', 6379));
            $res = $resdb->fetchAll();
            foreach ($res as $sp_anticorps) {
                $sp = $sp_anticorps[1];
                $redis->set("pfm:$sp:antibodies", $sp_anticorps[0]);
            }
            $redis->close();
        }
        Configuration::getLogger()->debug('set ac_anticorps counters, done!');
    }

    public function repair371()
    {
        Configuration::getLogger()->info("Run repair script 371 (PR #371)");
        $this->addColumn("users_info", "organization", "varchar(255)", "");
        Configuration::getLogger()->info("Run repair script 371 (PR #371)");
    }

    public function repair499()
    {
        Configuration::getLogger()->info("Run repair script 499 (PR #499)");
        $sql = "alter table se_order modify column date_open date NULL";
        $this->runRequest($sql);
        $sql = "alter table se_order modify column date_close date NULL";
        $this->runRequest($sql);
        $sql = "update se_order set date_close=null where date_close='0000-00-00'";
        $this->runRequest($sql);
        $sql = "update se_order set date_open=null where date_open='0000-00-00'";
        $this->runRequest($sql);
        Configuration::getLogger()->info("Run repair script 499 (PR #499)");
    }

    public function upgrade_v0_v1()
    {
        Configuration::getLogger()->debug("[db] Old existing db patch");
        $modelMainMenuPatch = new CoreMainMenuPatch();
        $modelMainMenuPatch->patch();

        $model2 = new UsersPatch();
        $model2->patch();

        Configuration::getLogger()->debug("[db] Old existing db did not set id as primary/auto-increment");
        $modelSpaceUser = new CoreSpaceUser();
        $sql = "alter table core_j_spaces_user drop column id";
        $modelSpaceUser->runRequest($sql);
        $sql = "alter table core_j_spaces_user add column id int not null auto_increment primary key";
        $modelSpaceUser->runRequest($sql);
    }

    public function upgrade_v1_v2()
    {
        Configuration::getLogger()->debug("[db] Add core_spaces shortname, contact, support");
        $cp = new CoreSpace();
        $cp->addColumn('core_spaces', 'shortname', "varchar(30)", '');
        $cp->addColumn('core_spaces', 'contact', "varchar(100)", '');
        $cp->addColumn('core_spaces', 'support', "varchar(100)", '');
        $spaces = $cp->getSpaces('id');
        foreach ($spaces as $space) {
            if (!$space['shortname']) {
                $shortname = $space['name'];
                $shortname = strtolower($shortname);
                $shortname = str_replace(" ", "", $shortname);
                $cp->setShortname($space['id'], $shortname);
            }
        }
        Configuration::getLogger()->debug("[db] Add core_spaces shortname, contact, support, done!");

        Configuration::getLogger()->debug("[stats] import stats");
        $cp = new CoreSpace();
        $statHandler = new EventHandler();
        $spaces = $cp->getSpaces('id');
        foreach ($spaces as $space) {
            $statHandler->spaceCreate(['space' => ['id' => $space['id']]]);
            $spaceUsers = $cp->getUsers($space['id']);
            foreach ($spaceUsers as $spaceUser) {
                $statHandler->spaceUserJoin([
                    'space' => ['id' => $space['id']],
                    'user' => ['id' => $spaceUser['id']]
                ]);
            }
        }
        Configuration::getLogger()->debug("[stats] import stats done!");


        Configuration::getLogger()->debug("[users] add apikey");
        $cu = new CoreUser();
        $cu->addColumn("core_users", "apikey", "varchar(30)", "");
        $allUsers = $cu->getAll();
        foreach ($allUsers as $user) {
            if ($user['login'] == Configuration::get('admin_user') && $user['apikey'] != "") {
                continue;
            }
            $cu->newApiKey($user['id']);
        }
        Configuration::getLogger()->debug("[users] add apikey done!");

        Configuration::getLogger()->debug("[adminmenu] remove update");
        $cam = new CoreAdminMenu();
        $cam->removeAdminMenu("Update");
        Configuration::getLogger()->debug("[adminmenu] remove update done!");
    }

    public function upgrade_v2_v3()
    {
        Configuration::getLogger()->debug('[id_space] set space identifier on objects');
        $sql = "SELECT * FROM `re_info`;";
        $resdb = $this->runRequest($sql)->fetchAll();
        foreach ($resdb as $res) {
            $sql = "UPDATE bk_access SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_calendar_entry SET id_space=? WHERE resource_id=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_calquantities SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_owner_prices SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_packages SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_prices SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_restrictions SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE bk_calsupinfo SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE re_event SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
            $sql = "UPDATE re_resps SET id_space=? WHERE id_resource=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }

        $sql = "SELECT * FROM `re_category`;";
        $resdb = $this->runRequest($sql)->fetchAll();
        foreach ($resdb as $res) {
            $sql = "UPDATE bk_authorization SET id_space=? WHERE resource_id=?";
            $this->runRequest($sql, array($res['id_space'], $res['id']));
        }

        $sql = "SELECT * FROM `bk_packages`;";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                $sql = "UPDATE bk_j_packages_prices SET id_space=? WHERE id_package=?";
                $this->runRequest($sql, array($res['id_space'], $res['id_package']));
            }
        }

        $sql = "SELECT * FROM `bk_calendar_entry`;";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                if ($res['period_id']) {
                    $sql = "UPDATE bk_calendar_period SET id_space=? WHERE id=?";
                    $this->runRequest($sql, array($res['id_space'], $res['period_id']));
                }
            }
        }

        $sql = "SELECT * FROM `re_area`;";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                $sql = "UPDATE bk_bookingcss SET id_space=? WHERE id_area=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
                $sql = "UPDATE bk_schedulings SET id_space=?, id_rearea=? WHERE id=?";
                $this->runRequest($sql, array($res['id_space'], $res['id'], $res['id']));
            }
        }

        $sql = "SELECT * FROM `bj_collections`;";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                $sql = "UPDATE bj_j_collections_notes SET id_space=? WHERE id_collection=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
            }
        }

        $sql = "SELECT * FROM `bj_notes`;";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                $sql = "UPDATE bj_events SET id_space=? WHERE id_note=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
                $sql = "UPDATE bj_tasks SET id_space=? WHERE id_note=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
                $sql = "UPDATE bj_tasks_history SET id_space=? WHERE id_note=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
            }
        }

        $sql = "SELECT * FROM `ca_categories`;";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                $sql = "UPDATE ca_entries SET id_space=? WHERE id_category=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
            }
        }

        $sql = "SELECT * FROM `cl_clients`;";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                $sql = "UPDATE cl_addresses SET id_space=? WHERE id=?";
                $this->runRequest($sql, array($res['id_space'], $res['address_invoice']));
                $this->runRequest($sql, array($res['id_space'], $res['address_delivery']));
                $sql = "UPDATE cl_j_client_user SET id_space=? WHERE id_client=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
            }
        }

        $sql = "SELECT * FROM `in_invoice`;";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                $sql = "UPDATE in_invoice_item SET id_space=? WHERE id_invoice=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
            }
        }

        $sql = "SELECT * FROM `qo_quotes`;";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                $sql = "UPDATE qo_quoteitems SET id_space=? WHERE id_quote=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
            }
        }

        $sql = "SELECT * FROM `re_event`;";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                $sql = "UPDATE re_event_data SET id_space=? WHERE id_event=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
            }
        }

        $sql = "SELECT * FROM `re_category`;";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                $sql = "UPDATE re_visas SET id_space=? WHERE id_resource_category=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
            }
        }

        $sql = "SELECT * FROM `se_services`;";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                $sql = "UPDATE se_prices SET id_space=? WHERE id_service=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
                $sql = "UPDATE se_purchase_item SET id_space=? WHERE id_service=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
                // update static array to match db state
                $sem = new SeServiceType();
                $sem->updateServiceTypesReferences();
                $sql = "UPDATE se_order_service SET id_space=? WHERE id_service=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
                $sql = "UPDATE se_project_service SET id_space=? WHERE id_service=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
                $sql = "UPDATE se_purchase_item SET id_space=? WHERE id_service=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
            }
        }

        $sql = "SELECT * FROM `stock_cabinets`;";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                $sql = "UPDATE stock_shelf SET id_space=? WHERE id_cabinet=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
            }
        }

        $sql = "SELECT * FROM `es_sales`;";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                $sql = "UPDATE es_sale_entered_items SET id_space=? WHERE id_sale=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
                $sql = "UPDATE es_sale_history SET id_space=? WHERE id_sale=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
                $sql = "UPDATE es_sale_items SET id_space=? WHERE id_sale=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
                $sql = "UPDATE es_sale_invoice_items SET id_space=? WHERE id_sale=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
            }
        }

        $sql = "SELECT * FROM `ac_anticorps`";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            while ($res = $resdb->fetch()) {
                $sql = "UPDATE ac_j_tissu_anticorps SET id_space=? WHERE id_anticorps=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
                $sql = "UPDATE ac_j_user_anticorps SET id_space=? WHERE id_anticorps=?";
                $this->runRequest($sql, array($res['id_space'], $res['id']));
            }
        }


        // Check
        $sql = "show tables";
        $tables = $this->runRequest($sql)->fetchAll();
        foreach ($tables as $t) {
            $table = $t[0];
            $sql = "SELECT COUNT(*) as total FROM ".$table;
            $notEmpty = $this->runRequest($sql)->fetch();
            if ($notEmpty && intval($notEmpty['total']) > 0) {
                // if table not empty and has id_space=0
                $sql = "SELECT COUNT(*) as total FROM ".$table." WHERE id_space=0";
                $null_spaces = $this->runRequest($sql)->fetch();
                if ($null_spaces && intval($null_spaces['total']) > 0) {
                    Configuration::getLogger()->warning('[id_space] found null space references', ['table' => $table, 'total' => $null_spaces]);
                }
            }
        }

        Configuration::getLogger()->debug('[id_space] set space identifier on objects, done!');

        Configuration::getLogger()->debug('[virtual counter] init virtual counter');
        $counter = 0;
        $sql = "SELECT max(id_package) as counter FROM bk_owner_prices";
        $resdb = $this->runRequest($sql)->fetch();
        if ($resdb && $resdb['counter']) {
            $counter = intval($resdb['counter']);
        }
        $sql = "SELECT max(id_package) as counter FROM bk_packages";
        $resdb = $this->runRequest($sql)->fetch();
        if ($resdb && intval($resdb['counter']) > $counter) {
            $counter = intval($resdb['counter']);
        }
        $sql = "SELECT max(id_package) as counter FROM bk_prices";
        $resdb = $this->runRequest($sql)->fetch();
        if ($resdb && intval($resdb['counter']) > $counter) {
            $counter = intval($resdb['counter']);
        }
        $sql = "SELECT max(id_supinfo) as counter FROM bk_calsupinfo";
        $resdb = $this->runRequest($sql)->fetch();
        if ($resdb && intval($resdb['counter']) > $counter) {
            $counter = intval($resdb['counter']);
        }
        $sql = "SELECT max(id_quantity) as counter FROM bk_calquantities";
        $resdb = $this->runRequest($sql)->fetch();
        if ($resdb && intval($resdb['counter']) > $counter) {
            $counter = intval($resdb['counter']);
        }

        $i = 0;
        while ($i <= $counter) {
            $cvm = new CoreVirtual();
            $cvm->new('import');
            $i++;
        }

        Configuration::getLogger()->debug('[virtual counter] init virtual counter, done!');

        Configuration::getLogger()->debug('set ac_anticorps counters');
        $sql = "SELECT max(no_h2p2) as counter, id_space FROM ac_anticorps GROUP BY id_space";
        $resdb = $this->runRequest($sql);
        if ($resdb!=null) {
            $redis = new Redis();
            $redis->pconnect(Configuration::get('redis_host', 'redis'), Configuration::get('redis_port', 6379));
            $res = $resdb->fetchAll();
            foreach ($res as $sp_anticorps) {
                $sp = $sp_anticorps[1];
                $redis->set("pfm:$sp:antibodies", $sp_anticorps[0]);
            }
            $redis->close();
        }
        Configuration::getLogger()->debug('set ac_anticorps counters, done!');

        if (Statistics::enabled()) {
            Configuration::getLogger()->debug("[stats] import calentry stats");
            $eventHandler = new EventHandler();
            $eventHandler->calentryImport();
            Configuration::getLogger()->debug('[stats] import calentry stats, done!');

            Configuration::getLogger()->debug("[stats] import invoice stats");
            $statHandler = new EventHandler();
            $statHandler->invoiceImport();
            Configuration::getLogger()->debug('[stats] import invoice stats, done!');
        }

        Configuration::getLogger()->debug('[core_users] fix column types');
        $sql = "alter table core_users modify phone varchar(255)";
        $this->runRequest($sql);
        $sql = "alter table core_users modify date_end_contract date";
        $this->runRequest($sql);
        $sql = "update core_users set date_end_contract=null where date_end_contract='0000-00-00'";
        $this->runRequest($sql);
        $sql = "alter table core_users modify date_last_login date";
        $this->runRequest($sql);
        $sql = "update core_users set date_last_login=null where date_last_login='0000-00-00'";
        $this->runRequest($sql);
        $sql = "alter table core_users modify apikey varchar(30)";
        $this->runRequest($sql);
        Configuration::getLogger()->debug('[core_users] fix column types, done!');

        Configuration::getLogger()->debug('[core_j_spaces_user] fix column types');
        $sql = "alter table core_j_spaces_user modify id_user int(11) NOT NULL";
        $this->runRequest($sql);
        $sql = "alter table core_j_spaces_user modify id_space int(11) NOT NULL";
        $this->runRequest($sql);
        $sql = "alter table core_j_spaces_user modify convention_url varchar(255)";
        $this->runRequest($sql);
        $sql = "alter table core_j_spaces_user modify date_contract_end date";
        $this->runRequest($sql);
        $sql = "alter table core_j_spaces_user modify date_convention date";
        $this->runRequest($sql);
        $sql = "update core_j_spaces_user set date_convention=null where date_convention='0000-00-00'";
        $this->runRequest($sql);
        $sql = "update core_j_spaces_user set date_contract_end=null where date_convention='0000-00-00'";
        $this->runRequest($sql);
        Configuration::getLogger()->debug('[core_j_spaces_user] fix column types, done!');

        Configuration::getLogger()->debug('[qo_quotes] fix column types');
        $sql = "alter table qo_quotes modify date_last_modified date";
        $this->runRequest($sql);
        $sql = "update qo_quotes set date_last_modified=null where date_last_modified='0000-00-00'";
        $this->runRequest($sql);
        Configuration::getLogger()->debug('[qo_quotes] fix column types, done!');

        Configuration::getLogger()->debug('[in_invoice] fix column types');
        $sql = "alter table in_invoice modify date_send date NULL";
        $this->runRequest($sql);
        $sql = "update in_invoice set date_send=null where date_send='0000-00-00'";
        $this->runRequest($sql);
        $sql = "alter table in_invoice modify column period_begin date NULL";
        $this->runRequest($sql);
        $sql = "alter table in_invoice modify column period_end date NULL";
        $this->runRequest($sql);
        $sql = "alter table in_invoice modify column date_generated date NULL";
        $this->runRequest($sql);
        Configuration::getLogger()->debug('[in_invoice] fix column types, done!');

        Configuration::getLogger()->debug('[bk_authorization] fix column types');
        $sql = "alter table bk_authorization modify `date` date";
        $this->runRequest($sql);
        $sql = "update bk_authorization set `date`=null where `date`='0000-00-00'";
        $this->runRequest($sql);
        $sql = "alter table bk_authorization modify `date_desactivation` date";
        $this->runRequest($sql);
        $sql = "update bk_authorization set `date_desactivation`=null where `date_desactivation`='0000-00-00'";
        $this->runRequest($sql);
        Configuration::getLogger()->debug('[bk_authorization] fix column types, done!');

        Configuration::getLogger()->debug('[core_pending_accounts] fix column types');
        $sql = "alter table core_pending_accounts modify `date` date";
        $this->runRequest($sql);
        $sql = "update core_pending_accounts set `date`=null where `date`='0000-00-00'";
        $this->runRequest($sql);
        Configuration::getLogger()->debug('[core_pending_accounts] fix column types, done!');

        Configuration::getLogger()->debug('[se_project] fix column types');
        $sql = "alter table se_project modify `samplereturndate` date";
        $this->runRequest($sql);
        $sql = "alter table se_project modify column date_open date NULL";
        $this->runRequest($sql);
        $sql = "alter table se_project modify column date_close date NULL";
        $this->runRequest($sql);
        $sql = "update se_project set `samplereturndate`=null where `samplereturndate`='0000-00-00'";
        $this->runRequest($sql);
        $sql = "update se_project set date_open=null where date_open='0000-00-00'";
        $this->runRequest($sql);
        $sql = "update se_project set date_close=null where date_close='0000-00-00'";
        $this->runRequest($sql);
        $sql = "update se_project_service set `date`=null where `date`='0000-00-00'";
        $this->runRequest($sql);

        Configuration::getLogger()->debug('[se_project] fix column types, done!');

        Configuration::getLogger()->debug('[bk_calendar_period] fix column types');
        $sql = "alter table bk_calendar_period modify `enddate` date";
        $this->runRequest($sql);
        $sql = "update bk_calendar_period set `enddate`=null where `enddate`='0000-00-00'";
        $this->runRequest($sql);
        Configuration::getLogger()->debug('[bk_calendar_period] fix column types, done!');

        Configuration::getLogger()->debug('[space] remove super admin from spaces admins');
        $cum = new CoreUser();
        $superAdmins = $cum->superAdmins();
        foreach ($superAdmins as $superAdmin) {
            $sql = "DELETE FROM core_j_spaces_user WHERE id_user=?";
            $this->runRequest($sql, array($superAdmin['id']));
        }
        Configuration::getLogger()->debug('[space] remove super admin from spaces admins, done!');

        Configuration::getLogger()->debug("[booking] add is_invoicing_unit");
        $bkqte = new BkCalQuantities();
        $bkqte->addColumn("bk_calquantities", "is_invoicing_unit", "int(1)", 0);
        Configuration::getLogger()->debug("[booking] add is_invoicing_unit done!");

        Configuration::getLogger()->debug("[users_info] add organization");
        $usersInfo = new UsersInfo();
        $usersInfo->addColumn("users_info", "organization", "varchar(255)", "");
        Configuration::getLogger()->debug("[users_info] add organization done!");
    }

    public function upgrade_v3_v4()
    {
        Configuration::getLogger()->debug('[booking] fix bk_calsupinfo mandatory column name');
        try {
            $sql = 'ALTER TABLE bk_calsupinfo RENAME COLUMN `       mandatory` TO `mandatory`';
            $this->runRequest($sql);
        } catch (Exception $e) {
            Configuration::getLogger()->debug('[booking] fix bk_calsupinfo mandatory column name: already ni good state, good!');
        }
        Configuration::getLogger()->debug('[booking] fix bk_calsupinfo mandatory column name, done!');

        Configuration::getLogger()->debug('[core] add txtcolor');
        $this->addColumn('core_space_menus', 'txtcolor', "varchar(7)", Constants::COLOR_WHITE);
        $this->addColumn('cl_pricings', 'txtcolor', "varchar(7)", Constants::COLOR_WHITE);
        Configuration::getLogger()->debug('[core] add txtcolor, done');

        Configuration::getLogger()->debug('[core] add space plan');
        $this->addColumn('core_spaces', 'plan', "int", '0');
        $this->addColumn('core_spaces', 'plan_expire', "int", '0');
        Configuration::getLogger()->debug('[core] add space plan, done');

        if (Statistics::enabled()) {
            $eventHandler = new EventHandler();
            $g = new Grafana();
            Configuration::getLogger()->debug('[grafana] create orgs');
            // Create org for existing spaces
            $cp = new CoreSpace();
            $spaces = $cp->getSpaces('id');
            foreach ($spaces as $space) {
                $g->createOrg($space);
            }
            Configuration::getLogger()->debug('[grafana] create orgs, done!');


            // import managers
            Configuration::getLogger()->debug('[grafana] import managers to grafana');
            $s = new CoreSpace();
            $spaces = $s->getSpaces('id');
            foreach ($spaces as $space) {
                $csu = new CoreSpaceUser();
                $managers = $csu->managersOrAdmin($space['id']);
                $g = new Grafana();
                $plan = new CorePlan($space['plan'], $space['plan_expire']);
                if ($plan->hasFlag(CorePlan::FLAGS_GRAFANA)) {
                    foreach ($managers as $manager) {
                        $u = new CoreUser();
                        $user = $u->getInfo($manager['id_user']);
                        Configuration::getLogger()->debug('[grafana] add user to org', ['org' => $space['shortname'], 'user' => $user['login']]);
                        $g->addUser($space['shortname'], $user['login'], $user['apikey']);
                    }
                } else {
                    Configuration::getLogger()->debug('[flags][disabled] ', ['space' => $space['name'] , 'flags' => [CorePlan::FLAGS_GRAFANA]]);
                }
                $eventHandler->spaceUserCount(["space" => ["id" => $space["id"]]]);
            }
            Configuration::getLogger()->debug('[grafana] import managers to grafana, done!');

            if (Statistics::enabled()) {
                Configuration::getLogger()->debug('[stats] import clients');
                $eventHandler = new EventHandler();
                $eventHandler->customerImport();
                Configuration::getLogger()->debug('[stats] import clients, done!');
            }
        }

        Configuration::getLogger()->debug('[qo_quotes] add column id_client');
        $sql = "ALTER TABLE qo_quotes ADD COLUMN id_client INT(11)";
        $this->runRequest($sql);
        Configuration::getLogger()->debug('[qo_quotes] add column id_client, done!');

        Configuration::getLogger()->debug('[qo_quotes] add column recipient_email');
        $sql = "ALTER TABLE qo_quotes ADD COLUMN recipient_email VARCHAR(100)";
        $this->runRequest($sql);
        Configuration::getLogger()->debug('[qo_quotes] add column recipient_email, done!');

        if (Statistics::enabled()) {
            Configuration::getLogger()->debug('[db] update grafana dashboards and sql views');
            $s = new CoreSpace();
            $spaces = $s->getSpaces('id');
            $statHandler = new EventHandler();
            foreach ($spaces as $space) {
                $statHandler->spaceCreate(['space' => ['id' => $space['id']]]);
            }
            Configuration::getLogger()->debug('[db] update grafana dashboards and sql views, done!');
        }

        Configuration::getLogger()->debug('[db] set core_j_spaces_user.date_contract_end to NULL if 0000-00-00');
        $sql = "UPDATE core_j_spaces_user SET date_contract_end=null WHERE date_contract_end='0000-00-00'";
        $this->runRequest($sql);
        Configuration::getLogger()->debug('[db] set core_j_spaces_user.date_contract_end to NULL if 0000-00-00, done!');

        Configuration::getLogger()->debug('[se_order] fix column types');
        $sql = "alter table se_order modify column date_open date NULL";
        $this->runRequest($sql);
        $sql = "alter table se_order modify column date_close date NULL";
        $this->runRequest($sql);
        $sql = "update se_order set date_close=null where date_close='0000-00-00'";
        $this->runRequest($sql);
        $sql = "update se_order set date_open=null where date_open='0000-00-00'";
        $this->runRequest($sql);
        Configuration::getLogger()->debug('[se_order] fix column types, done!');
    }

    /**
     * Get current database version
     */
    public function getRelease()
    {
        $sqlRelease = "SELECT * FROM `pfm_db`;";
        $reqRelease = $this->runRequest($sqlRelease);
        $release = null;
        if ($reqRelease && $reqRelease->rowCount() > 0) {
            $release = $reqRelease->fetch();
            return $release['version'];
        }
        return 0;
    }

    /**
     * Get expected database version
     */
    public function getVersion()
    {
        return DB_VERSION;
    }

    /**
     * Create the table
     *
     * @return PDOStatement
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `pfm_db` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `version` varchar(255) NOT NULL DEFAULT ".DB_VERSION.",
            PRIMARY KEY (`id`)
        );";

        $this->runRequest($sql);
    }

    public function needUpgrade(): array
    {
        $need = [];
        $upgradeFiles = scandir('db/upgrade');
        sort($upgradeFiles);

        foreach ($upgradeFiles as $f) {
            if (!str_ends_with($f, '.php')) {
                continue;
            }
            $sql = 'SELECT id FROM pfm_upgrade WHERE record=?';
            $record = str_replace('.php', '', $f);
            $res = $this->runRequest($sql, [$record]);

            if (!$res) {
                Configuration::getLogger()->error('request failed');
                $need[] = $f;
                continue;
            }
            if ($res->rowCount() > 0) {
                Configuration::getLogger()->debug('[db][upgrade] already applied', ['file' => $f]);
                continue;
            }
            $need[] = $f;
        }
        return $need;
    }

    public function scanUpgrades(int $from=-1)
    {
        if (!file_exists('db/upgrade')) {
            return;
        }
        $sql = "CREATE TABLE IF NOT EXISTS `pfm_upgrade` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `record` varchar(255) NOT NULL,
            `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);

        if ($from == 0) {
            Configuration::getLogger()->info("Installing from $from, resetting upgrades to apply...");
            $sql = 'DELETE FROM pfm_upgrade';
            $this->runRequest($sql);
        }

        $upgradeFiles = scandir('db/upgrade');
        sort($upgradeFiles);
        foreach ($upgradeFiles as $f) {
            if (!str_ends_with($f, '.php')) {
                continue;
            }
            $sql = 'SELECT id FROM pfm_upgrade WHERE record=?';
            $record = str_replace('.php', '', $f);
            $res = $this->runRequest($sql, [$record]);
            if (!$res) {
                Configuration::getLogger()->error('request failed');
                break;
            }
            if ($res->rowCount() > 0) {
                Configuration::getLogger()->info('[db][upgrade] already applied', ['file' => $f]);
                continue;
            }
            Configuration::getLogger()->info('[db][upgrade] applying', ['file' => $f]);
            try {
                include "db/upgrade/$f";
                $sql = 'INSERT INTO pfm_upgrade (record) VALUES (?)';
                $this->runRequest($sql, [$record]);
            } catch(Throwable $e) {
                Configuration::getLogger()->error("[db][upgrade] an error occured", ["error" => $e]);
                break;
            }
        }
    }

    /**
     * Sets base columns
     */
    public function base()
    {
        Configuration::getLogger()->info("[db] set base columns if not present");
        $sql = "show tables";
        $tables = $this->runRequest($sql)->fetchAll();
        foreach ($tables as $t) {
            $table = $t[0];
            $sql = "show index from `$table`";
            $indexes = $this->runRequest($sql)->fetchAll();
            $this->addColumn($table, "deleted", "int(1)", 0);
            $this->addColumn($table, "deleted_at", "DATETIME", "", true);
            $this->addColumn($table, "created_at", "TIMESTAMP", "INSERT_TIMESTAMP");
            $this->addColumn($table, "updated_at", "TIMESTAMP", "UPDATE_TIMESTAMP");
            $this->addColumn($table, "id_space", "int(11)", 0);

            $indexExists = false;
            foreach ($indexes as $index) {
                if ($index['Key_name'] == "idx_${table}_space") {
                    $indexExists = true;
                    Configuration::getLogger()->debug('[db] id_space index already exists');
                    break;
                }
            }
            if (!$indexExists) {
                Configuration::getLogger()->debug('[db] create id_space index');
                $space_index = "CREATE INDEX `idx_${table}_space` ON `$table` (`id_space`)";
                $this->runRequest($space_index);
            }
        }
        Configuration::getLogger()->info("[db] set base columns if not present, done!");
    }

    public function upgrade($from=-1)
    {
        $sqlRelease = "SELECT * FROM `pfm_db`;";
        $reqRelease = $this->runRequest($sqlRelease);


        $isNewRelease = false;
        $oldRelease = 0;
        $release = null;
        if ($reqRelease->rowCount() > 0) {
            $release = $reqRelease->fetch();
            if ($release['version'] != DB_VERSION) {
                $isNewRelease = true;
                $oldRelease = $release['version'];
            }
        } else {
            Configuration::getLogger()->info("[db] database release not set, setting it...", ["release" => 0]);
            $sql = "INSERT INTO pfm_db (version) VALUES(?)";
            $this->runRequest($sql, array(DB_VERSION));
            $isNewRelease = true;
            $reqRelease = $this->runRequest($sqlRelease);
            $release = $reqRelease->fetch();
            if ($from == -1) {
                Configuration::getLogger()->info("[db] fresh install, no migration needed");
                return;
            }
        }

        if ($from >= 0) {
            $oldRelease = $from;
            $isNewRelease = true;
        }


        // old migration stuff, now using db/upgrade files, keep for backward compatiblity
        if ($isNewRelease) {
            Configuration::getLogger()->info("[db] Need to migrate", ["oldrelease" => $oldRelease, "release" => DB_VERSION]);
            $updateFromRelease = $oldRelease;
            $updateToRelease = $updateFromRelease + 1;
            $updateOK = true;
            try {
                while ($updateFromRelease < DB_VERSION) {
                    Configuration::getLogger()->info("[db] Migrating", ["from" => $updateFromRelease, "to" => $updateToRelease]);
                    $upgradeMethod = "upgrade_v".$updateFromRelease."_v".$updateToRelease;
                    if (method_exists($this, $upgradeMethod)) {
                        try {
                            $this->$upgradeMethod();
                        } catch(Throwable $e) {
                            $updateOK = false;
                            Configuration::getLogger()->error("[db] Migration failed", ["from" => $updateFromRelease, "to" => $updateToRelease, "error" => $e]);
                            break;
                        }
                    } else {
                        Configuration::getLogger()->info("[db] No migration available", ["from" => $updateFromRelease, "to" => $updateToRelease]);
                    }

                    Configuration::getLogger()->info("[db] updating database version...", ["release" => $updateToRelease]);
                    $sql = "update pfm_db set version=? where id=?";
                    $this->runRequest($sql, array($updateToRelease, $release['id']));

                    $updateFromRelease = $updateToRelease;
                    $updateToRelease = $updateFromRelease + 1;
                }
            } catch(Throwable $e) {
                Configuration::getLogger()->error("[db] migration error", ["error" => $e->getMessage()]);
                $updateOK = false;
            }
            if ($updateOK) {
                Configuration::getLogger()->info("[db] database migration over");
            } else {
                Configuration::getLogger()->error("[db] database migration failed!");
            }
        } else {
            Configuration::getLogger()->info("[db] no migration needed");
        }
    }
}


/**
 * Class defining the Install model
 * to edit the config file and initialize de database
 *
 * @author Sylvain Prigent
 */
class CoreInstall extends Model
{
    public function createDatabase()
    {
        $modelCache = new FCache();
        $modelCache->freeTableURL();
        $modelCache->load();

        $modelConfig = new CoreConfig();
        $modelConfig->createTable();

        $modelConfig->initParam("admin_email", Configuration::get('admin_email', ''));
        $modelConfig->initParam("logo", "Modules/core/Theme/logo.jpg");
        $modelConfig->initParam("home_title", "Platform-Manager");
        $modelConfig->initParam("home_message", "Connection");

        $modelConfig->setParam("navbar_bg_color", "#404040");
        $modelConfig->setParam("navbar_bg_highlight", "#333333");
        $modelConfig->setParam("navbar_text_color", "#e3e2e4");
        $modelConfig->setParam("navbar_text_highlight", Constants::COLOR_WHITE);

        $modelUser = new CoreUser();
        $modelUser->createTable();
        $modelUser->installDefault();

        $modelUserS = new CoreUserSettings();
        $modelUserS->createTable();

        $moduleUserSpaceS = new CoreUserSpaceSettings();
        $moduleUserSpaceS->createTable();

        $modelMenu = new CoreAdminMenu();
        $modelMenu->createTable();
        $modelMenu->addCoreDefaultMenus();

        $modelProject = new CoreProjects();
        $modelProject->createTable();

        $modelSpace = new CoreSpace();
        $modelSpace->createTable();

        $modelModules = new CoreInstalledModules();
        $modelModules->createTable();

        $modelCoreMainMenu = new CoreMainMenu();
        $modelCoreMainMenu->createTable();

        $modelCoreMainSubMenu = new CoreMainSubMenu();
        $modelCoreMainSubMenu->createTable();

        $modelCoreMainMenuItem = new CoreMainMenuItem();
        $modelCoreMainMenuItem->createTable();

        $modelPending = new CorePendingAccount();
        $modelPending->createTable();

        $modelCoreSpaceUser = new CoreSpaceUser();
        $modelCoreSpaceUser->createTable();

        $modelCoreSpaceAccessOptions = new CoreSpaceAccessOptions();
        $modelCoreSpaceAccessOptions->createTable();

        $modelOpenid = new CoreOpenId();
        $modelOpenid-> createTable();

        $modelStatistics = new BucketStatistics();
        $modelStatistics->createTable();

        if (Statistics::enabled()) {
            Configuration::getLogger()->debug('[stats] create pfm influxdb bucket and add admin user');
            $statHandler = new Statistics();
            $pfmOrg = Configuration::get('influxdb_org', 'pfm');
            $statHandler->createDB($pfmOrg);
            $eventHandler = new EventHandler();
            $eventHandler->spaceCount(null);
            // create org
            $g = new Grafana();
            $g->createOrg(['shortname' => $pfmOrg]);
            $u = new CoreUser();
            $adminUser = $u->getUserByLogin(Configuration::get('admin_user'));
            $g->addUser($pfmOrg, $adminUser['login'], $adminUser['apikey']);
            Configuration::getLogger()->debug('[stats] create pfm influxdb bucket and add admin user, done!');
        }

        $modelCoreFiles = new CoreFiles();
        $modelCoreFiles->createTable();

        $modelVirtual = new CoreVirtual();
        $modelVirtual->createTable();
        $modelHistory = new CoreHistory();
        $modelHistory->createTable();

        $modelStar = new CoreStar();
        $modelStar->createTable();
        $modelMail = new CoreMail();
        $modelMail->createTable();

        if (!file_exists('data/conventions/')) {
            mkdir('data/conventions/', 0755, true);
        }
    }
    /**
     * Test if the database informations are correct
     *
     * @param string $sql_host Host of the database (ex: localhost)
     * @param string $login Login to connect to the database (ex: root)
     * @param string $password Password to connect to the database
     * @param string $db_name Name of the database
     * @return string error message
     */
    public function testConnection($sql_host, $login, $password, $db_name)
    {
        try {
            $dsn = 'mysql:host=' . $sql_host . ';dbname=' . $db_name . ';charset=utf8';
            $testdb = new PDO($dsn, $login, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
            $testdb->exec("SHOW TABLES");
            return 'success';
        } catch (PfmDbException $e) {
            return $e->getMessage();
        }
    }

    /**
     * Save the database connection information into the config file
     *
     * @param string $sql_host Host of the database (ex: localhost)
     * @param string $login Login to connect to the database (ex: root)
     * @param string $password Password to connect to the database
     * @param string $db_name Name of the database
     * @return boolean false if unable to write in the file
     */
    public function writedbConfig($sql_host, $login, $password, $db_name)
    {
        $dsn = '\'mysql:host=' . $sql_host . ';dbname=' . $db_name . ';charset=utf8\'';

        $fileURL = Configuration::getConfigFile();
        $res = false;
        try {
            $res = $this->editConfFile($fileURL, $dsn, $login, $password);
        } catch(Throwable $e) {
            Configuration::getLogger()->debug('failed to edit config file '.$fileURL, ['err' => $e]);
        }
        return $res;
    }

    /**
     * Internal function that implement the config file edition
     *
     * @param string $fileURL URL of the configuration file
     * @param string $dsn Connection informations for the PDO connection
     * @param string $login Login to connect to the database (ex: root)
     * @param string $password Password to connect to the database
     * @return boolean
     */
    protected function editConfFile($fileURL, $dsn, $login, $password)
    {
        $handle = @fopen($fileURL, "r");
        $outContent = '';
        if ($handle) {
            while (($buffer = fgets($handle, 4096)) !== false) {
                // replace dsn
                $outbuffer1 = $this->replaceContent($buffer, 'dsn', $dsn);
                if ($outbuffer1 != "") {
                    $outContent = $outContent . $outbuffer1;
                    continue;
                }

                // replace login
                $outbuffer2 = $this->replaceContent($buffer, 'login', $login);
                if ($outbuffer2 != "") {
                    $outContent = $outContent . $outbuffer2;
                    continue;
                }

                // replace pwd
                $outbuffer3 = $this->replaceContent($buffer, 'pwd', $password);
                if ($outbuffer3 != "") {
                    $outContent = $outContent . $outbuffer3;
                    continue;
                }

                $outContent = $outContent . $buffer;
            }
            if (!feof($handle)) {
                echo "Erreur: fgets() failed \n";
            }
            fclose($handle);
        } else {
            return false;
        }

        // save the new cong file
        $fp = fopen($fileURL, 'w');
        fwrite($fp, $outContent);
        fclose($fp);
        return true;
    }

    private function replaceContent($buffer, $varName, $varContent)
    {
        $content = "";
        $pos = strpos($buffer, $varName);
        if ($pos === false) {
            return $content;
        }
        if ($pos == 0) {
            $content = $varName . ' = ' . $varContent . PHP_EOL;
        }
        return $content;
    }
}

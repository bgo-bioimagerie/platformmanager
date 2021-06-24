<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreConfig.php';

/**
 * Class defining the User settings model.
 * This store the settings of all the users
 *
 * @author Sylvain Prigent
 */
class CoreUserSettings extends Model {

    /**
     * Create the user settings table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `core_users_settings` (
		`user_id` int(11) NOT NULL,
		`setting` varchar(30) NOT NULL DEFAULT '',
		`value` varchar(40) NOT NULL DEFAULT ''
		);";

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    public function mergeUsers($users) {
        for ($i = 1; $i < count($users); $i++) {
            $sql = "UPDATE core_users_settings SET user_id=? WHERE user_id=?";
            $this->runRequest($sql, array($users[0], $users[$i]));
        }
    }

    /**
     * Get the settings of a given user
     * @param number $user_id User ID
     * @return multitype: Use settings
     */
    public function getUserSettings($user_id) {
        $sql = "select setting, value  from core_users_settings where user_id=?";
        $user = $this->runRequest($sql, array($user_id));
        $res = $user->fetchAll();

        $out = array();
        foreach ($res as $r) {
            $out[$r["setting"]] = $r["value"];
        }
        return $out;
    }

    /**
     * Get a given setting of a given user
     * @param number $user_id User ID
     * @param string $setting Setting key
     * @return mixed
     */
    public function getUserSetting($user_id, $setting) {
        $sql = "select value from core_users_settings where user_id=? and setting=?";
        $user = $this->runRequest($sql, array($user_id, $setting));
        $tmp = $user->fetch();
        return $tmp[0];
    }

    /**
     * Set (add if not exists, update otherwise) a setting for a given user
     * @param number $user_id User ID
     * @param string $setting Setting key
     * @param string $value Setting value
     */
    public function setSettings($user_id, $setting, $value) {
        if (!$this->isSetting($user_id, $setting)) {
            $this->addSetting($user_id, $setting, $value);
        } else {
            $this->updateSetting($user_id, $setting, $value);
        }
    }

    /**
     * Check if a setting exists for a given user
     * @param number $user_id User ID
     * @param string $setting Setting key
     * @return boolean
     */
    protected function isSetting($user_id, $setting) {
        $sql = "select * from core_users_settings where user_id=? and setting=?";
        $req = $this->runRequest($sql, array($user_id, $setting));
        return ($req->rowCount() == 1) ? true : false;
    }

    /**
     * Add a setting for a given user
     * @param number $user_id User ID
     * @param string $setting Setting key
     * @param string $value Setting value
     */
    protected function addSetting($user_id, $setting, $value) {
        $sql = "insert into core_users_settings (user_id, setting, value)
				 VALUES(?,?,?)";
        $this->runRequest($sql, array($user_id, $setting, $value));
    }

    /**
     * Update a setting for a given user
     * @param number $user_id User ID
     * @param string $setting Setting key
     * @param string $value Setting value
     */
    protected function updateSetting($user_id, $setting, $value) {
        $sql = "update core_users_settings set value=? where user_id=? and setting=?";
        $this->runRequest($sql, array($value, $user_id, $setting));
    }

    /**
     * Set user setting into a session variable
     */
    public function updateSessionSettingVariable() {
        // add the user settings to the session
        $settings = $this->getUserSettings($_SESSION["id_user"]);
        $_SESSION["user_settings"] = $settings;
    }

}

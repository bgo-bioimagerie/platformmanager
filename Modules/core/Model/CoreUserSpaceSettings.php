<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreConfig.php';

/**
 * Class defining the User per space settings model.
 * This store the settings of all the users
 *
 * @author Sylvain Prigent
 */
class CoreUserSpaceSettings extends Model {

    /**
     * Create the user settings table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `core_user_space_settings` (
		`user_id` int(11) NOT NULL,
		`setting` varchar(30) NOT NULL DEFAULT '',
		`value` varchar(40) NOT NULL DEFAULT '',
        `id_space` int(11) NOT NULL
		);";

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    /**
     * Get the settings of a given user
     * @param number $id_space space ID
     * @param number $user_id User ID
     * @return multitype: Use settings
     */
    public function getUserSettings($id_space, $user_id) {
        $sql = "select setting, value  from core_user_space_settings where user_id=? AND id_space=?";
        $user = $this->runRequest($sql, array($user_id, $id_space));
        $res = $user->fetchAll();

        $out = array();
        foreach ($res as $r) {
            $out[$r["setting"]] = $r["value"];
        }
        return $out;
    }

    /**
     * Get all users settings for input setting and optional value
     */
    public function getUsersForSetting($id_space, $setting, $value=null) {
        if($value == null) {
            $sql = "select * from core_user_space_settings where setting=? and id_space=?";
            $user = $this->runRequest($sql, array($setting, $id_space));
            return $user->fetchAll();
        }
        $sql = "select * from core_user_space_settings where setting=? and value=? and id_space=?";
        $user = $this->runRequest($sql, array($setting, $value, $id_space));
        return $user->fetchAll();
    }

    /**
     * Get a given setting of a given user
     * @param number $id_space space ID
     * @param number $user_id User ID
     * @param string $setting Setting key
     * @return mixed
     */
    public function getUserSetting($id_space, $user_id, $setting) {
        $sql = "select value from core_user_space_settings where user_id=? and setting=? and id_space=?";
        $user = $this->runRequest($sql, array($user_id, $setting, $id_space));
        $tmp = $user->fetch();
        return $tmp[0];
    }

    /**
     * Set (add if not exists, update otherwise) a setting for a given user
     * @param number $id_space space ID
     * @param number $user_id User ID
     * @param string $setting Setting key
     * @param string $value Setting value
     */
    public function setUserSettings($id_space, $user_id, $setting, $value) {
        if (!$this->isSetting($id_space, $user_id, $setting)) {
            $this->addSetting($id_space, $user_id, $setting, $value);
        } else {
            $this->updateSetting($id_space, $user_id, $setting, $value);
        }
    }

    /**
     * Check if a setting exists for a given user
     * @param number $id_space space ID
     * @param number $user_id User ID
     * @param string $setting Setting key
     * @return boolean
     */
    protected function isSetting($id_space, $user_id, $setting) {
        $sql = "select * from core_user_space_settings where user_id=? and setting=? and id_space=?";
        $req = $this->runRequest($sql, array($user_id, $setting, $id_space));
        return $req->rowCount() == 1;
    }

    /**
     * Add a setting for a given user
     * @param number $id_space space ID
     * @param number $user_id User ID
     * @param string $setting Setting key
     * @param string $value Setting value
     */
    protected function addSetting($id_space, $user_id, $setting, $value) {
        $sql = "insert into core_user_space_settings (id_space, user_id, setting, value)
				 VALUES(?,?,?,?)";
        $this->runRequest($sql, array($id_space, $user_id, $setting, $value));
    }

    /**
     * Update a setting for a given user
     * @param number $id_space space ID
     * @param number $user_id User ID
     * @param string $setting Setting key
     * @param string $value Setting value
     */
    protected function updateSetting($id_space, $user_id, $setting, $value) {
        $sql = "update core_user_space_settings set value=? where user_id=? and setting=? and id_space=?";
        $this->runRequest($sql, array($value, $user_id, $setting, $id_space));
    }

    /**
     * Set user setting into a session variable
     * @param number $id_space space ID
     */
    public function updateSessionSettingVariable($id_space) {
        // add the user settings to the session
        $settings = $this->getUserSettings($id_space, $_SESSION["id_user"]);
        $_SESSION["user_space_settings"] = $settings;
    }

}

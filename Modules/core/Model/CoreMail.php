<?php

require_once 'Framework/Model.php';

class CoreMail extends Model
{
    public function __construct()
    {
        $this->tableName = "core_mail_unsubscribe";
    }

    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `core_mail_unsubscribe` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `id_space` int(11) NOT NULL,
        `id_user` varchar(30) NOT NULL,
        `module` varchar(50) NOT NULL,
        PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);
    }

    public function unsubscribed($id_user, $id_space, $module)
    {
        $sql = "SELECT id FROM core_mail_unsubscribe WHERE id_user=? AND id_space=? AND module=?";
        $req = $this->runRequest($sql, array($id_user, $id_space, $module));
        if ($req && $req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function unsubscribe($id_user, $id_space, $module)
    {
        if (!$this->unsubscribed($id_user, $id_space, $module)) {
            $sql = "INSERT INTO core_mail_unsubscribe (id_user, id_space, module) VALUES (?, ?, ?)";
            $this->runRequest($sql, array($id_user, $id_space, $module));
        }
    }

    public function subscribe($id_user, $id_space, $module)
    {
        $sql = "DELETE FROM core_mail_unsubscribe WHERE id_user=? AND id_space=? AND module=?";
        $this->runRequest($sql, array($id_user, $id_space, $module));
    }
}

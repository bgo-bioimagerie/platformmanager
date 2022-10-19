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

    public function unsubscribed($idUser, $idSpace, $module)
    {
        $sql = "SELECT id FROM core_mail_unsubscribe WHERE id_user=? AND id_space=? AND module=?";
        $req = $this->runRequest($sql, array($idUser, $idSpace, $module));
        if ($req && $req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function unsubscribe($idUser, $idSpace, $module)
    {
        if (!$this->unsubscribed($idUser, $idSpace, $module)) {
            $sql = "INSERT INTO core_mail_unsubscribe (id_user, id_space, module) VALUES (?, ?, ?)";
            $this->runRequest($sql, array($idUser, $idSpace, $module));
        }
    }

    public function subscribe($idUser, $idSpace, $module)
    {
        $sql = "DELETE FROM core_mail_unsubscribe WHERE id_user=? AND id_space=? AND module=?";
        $this->runRequest($sql, array($idUser, $idSpace, $module));
    }
}

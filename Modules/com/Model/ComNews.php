<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class ComNews extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "com_news";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("title", "varchar(250)", "");
        $this->setColumnsInfo("content", "TEXT", "");
        $this->setColumnsInfo("media", "TEXT", "");
        $this->setColumnsInfo("date", "DATE", "");
        $this->setColumnsInfo("expires", "DATE", "");
        $this->primaryKey = "id";
    }

    public function getForSpace($id_space, $limit = -1) {
        if ($limit <= 0) {
            $sql = "SELECT * FROM com_news WHERE id_space=?";
            $req = $this->runRequest($sql, array($id_space));
        } else {
            $sql = "SELECT * FROM com_news WHERE id_space=? LIMIT ?";
            $req = $this->runRequest($sql, array($id_space, $limit));
        }
        return $req->fetchAll();
    }

    public function getByDate($id_space, $limit = -1) {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM com_news WHERE id_space=? AND expires>=? ORDER BY date ASC LIMIT ".$limit.";";
        $req = $this->runRequest($sql, array($id_space, $today));
        return $req->fetchAll();
    }

    public function set($id, $id_space, $title, $content, $date, $expire) {
        if ($this->isNews($id)) {
            $sql = "UPDATE com_news SET id_space=?, title=?, content=?, date=?, expires=?"
                    . " WHERE id=?";
            $this->runRequest($sql, array($id_space, $title, $content, $date, $expire, $id));
            return $id;
        } else {
            $sql = "INSERT INTO com_news (id_space, title, content, date, expires) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($id_space, $title, $content, $date, $expire));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function isNews($id) {
        $sql = "SELECT id FROM com_news WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function setMedia($id, $url) {
        $sql = "UPDATE com_news SET media=? WHERE id=?";
        $this->runRequest($sql, array($url, $id));
    }

    public function getMedia($id) {
        $sql = "SELECT media FROM com_news WHERE id=?";
        $req = $this->runRequest($sql, array($id))->fetch();
        return $req[0];
    }

    public function get($id) {
        if (!$id) {
            return array(
                "id" => 0,
                "id_space" => 0,
                "title" => "",
                "content" => "",
                "media" => "",
                "date" => date('Y-m-d'),
                "expires" => "0000-00-00"
            );
        }
        $sql = "SELECT * FROM com_news WHERE id=?";
        $req = $this->runRequest($sql, array($id))->fetch();
        return $req;
    }

    public function delete($id) {
        // remove the file
        $sql = "SELECT * FROM com_news WHERE id=?";
        $info = $this->runRequest($sql, array($id))->fetch();
        if (file_exists($info["media"])) {
            unlink($info["media"]);
        }

        // remove the entry
        $sql2 = "DELETE FROM com_news WHERE id=?";
        $this->runRequest($sql2, array($id));
    }

}
